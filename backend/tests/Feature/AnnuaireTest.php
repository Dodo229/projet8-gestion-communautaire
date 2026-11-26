<?php

namespace Tests\Feature;

use App\Models\Categorie;
use App\Models\Competence;
use App\Models\NiveauEtude;
use App\Models\Profil;
use App\Models\Secteur;
use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PARTIE MEMBRE 4 : tests automatiques de la recherche et de la consultation.
 * Lancer avec : php artisan test
 *
 * Chaque test suit le schéma « préparer, agir, vérifier » :
 *   1. on crée des profils en base (préparer)
 *   2. on appelle l'API comme le ferait React (agir)
 *   3. on vérifie la réponse (vérifier)
 * RefreshDatabase vide la base avant chaque test : ils ne se gênent pas entre eux.
 * Les tests tournent sur une base SQLite en mémoire : votre base MySQL n'est jamais touchée.
 */
class AnnuaireTest extends TestCase
{
    use RefreshDatabase;

    /** Crée un profil de test. $surcharge permet de changer n'importe quelle valeur. */
    private function profil(string $statut = 'valide', array $surcharge = []): Profil
    {
        $compte = Utilisateur::create(['email' => uniqid() . '@test.local', 'mot_de_passe' => 'MotDePasse-123']);

        $profil = $compte->profil()->create($surcharge + [
            'categorie_id' => Categorie::firstOrCreate(['libelle' => 'Artisans'])->id,
            'secteur_id' => Secteur::firstOrCreate(['libelle' => 'Artisanat'])->id,
            'niveau_etude_id' => NiveauEtude::firstOrCreate(['libelle' => 'Licence'], ['rang' => 3])->id,
            'nom' => 'Dupont', 'prenoms' => 'Jean', 'metier' => 'Menuisier', 'localisation' => 'Centre-ville',
            'telephone' => '+229 01 00 00 00 00', 'contact_public' => false,
            'consentement_le' => now(),
        ]);
        $profil->forceFill(['statut' => $statut])->save();

        return $profil;
    }

    public function test_l_annuaire_ne_montre_que_les_profils_valides(): void
    {
        $this->profil('valide');
        $this->profil('en_attente');
        $this->profil('rejete');
        $this->profil('suspendu');

        $this->getJson('/api/v1/profils')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_filtre_par_secteur(): void
    {
        $this->profil('valide');                                                    // secteur Artisanat
        $numerique = Secteur::create(['libelle' => 'Numérique']);
        $this->profil('valide', ['secteur_id' => $numerique->id, 'metier' => 'Développeur']);

        $reponse = $this->getJson('/api/v1/profils?secteur_id=' . $numerique->id);

        $reponse->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.metier', 'Développeur');
    }

    public function test_les_filtres_se_combinent(): void
    {
        $this->profil('valide', ['localisation' => 'Quartier Nord']);
        $this->profil('valide', ['localisation' => 'Quartier Sud']);

        // Même secteur, mais une seule localisation correspond
        $secteur = Secteur::first()->id;
        $this->getJson("/api/v1/profils?secteur_id=$secteur&localisation=nord")
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.localisation', 'Quartier Nord');
    }

    public function test_la_recherche_libre_trouve_un_debut_de_mot(): void
    {
        $this->profil('valide', ['metier' => 'Ingénieur civil']);
        $this->profil('valide', ['metier' => 'Couturière']);

        $this->getJson('/api/v1/profils?q=ingé')->assertJsonCount(1, 'data');
    }

    public function test_la_recherche_ne_contourne_pas_le_filtre_de_statut(): void
    {
        // Un profil NON validé qui correspond à la recherche ne doit jamais apparaître
        $this->profil('en_attente', ['metier' => 'Plombier']);

        $this->getJson('/api/v1/profils?q=plombier')->assertJsonCount(0, 'data');
    }

    public function test_filtre_par_competence(): void
    {
        $avec = $this->profil('valide');
        $avec->competences()->attach(Competence::create(['libelle' => 'Soudure'])->id);
        $this->profil('valide');

        $this->getJson('/api/v1/profils?competence=Soudure')->assertJsonCount(1, 'data');
    }

    public function test_la_pagination_decoupe_les_resultats_par_12(): void
    {
        foreach (range(1, 13) as $i) {
            $this->profil('valide', ['nom' => "Nom$i"]);
        }

        $page1 = $this->getJson('/api/v1/profils');
        $page1->assertJsonCount(12, 'data')->assertJsonPath('total', 13)->assertJsonPath('last_page', 2);

        $this->getJson('/api/v1/profils?page=2')->assertJsonCount(1, 'data');
    }

    public function test_le_detail_d_un_profil_non_valide_est_introuvable(): void
    {
        $this->getJson('/api/v1/profils/' . $this->profil('valide')->id)->assertOk();
        $this->getJson('/api/v1/profils/' . $this->profil('en_attente')->id)->assertNotFound();
    }

    public function test_le_telephone_est_masque_pour_un_visiteur_et_visible_pour_un_connecte(): void
    {
        $profil = $this->profil('valide', ['contact_public' => false]);

        // Visiteur : le téléphone est absent
        $this->getJson("/api/v1/profils/{$profil->id}")->assertJsonPath('telephone', null);

        // Utilisateur connecté : le téléphone apparaît
        $autre = Utilisateur::create(['email' => 'connecte@test.local', 'mot_de_passe' => 'MotDePasse-123']);
        $this->actingAs($autre)->getJson("/api/v1/profils/{$profil->id}")->assertJsonPath('telephone', '+229 01 00 00 00 00');
    }

    public function test_aucune_donnee_sensible_dans_la_reponse_publique(): void
    {
        $profil = $this->profil('valide');
        $json = $this->getJson("/api/v1/profils/{$profil->id}")->json();

        $this->assertArrayNotHasKey('email', $json);
        $this->assertArrayNotHasKey('motif_rejet', $json);
        $this->assertArrayNotHasKey('utilisateur_id', $json);
    }
}
