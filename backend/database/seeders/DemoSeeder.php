<?php

namespace Database\Seeders;

use App\Models\Categorie;
use App\Models\Competence;
use App\Models\NiveauEtude;
use App\Models\Profil;
use App\Models\Secteur;
use App\Models\Utilisateur;
use Illuminate\Database\Seeder;

/**
 * FOURNI PAR LE MEMBRE 1 : profils fictifs pour tester la recherche.
 * 35 profils (32 validés, 3 en attente) : assez pour avoir 3 pages de résultats (12 par page).
 * Mot de passe de tous les comptes de démonstration : Demo-2026-pass
 * Exemple de compte pour tester le téléphone visible : houngbo0@exemple.local
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // nom, prénoms, métier, catégorie, secteur, niveau, quartier, présentation, compétences, statut, expériences
        $donnees = [
            ['Houngbo', 'Adjovi', 'Secrétaire générale de mairie', 'Cadres administratifs', 'Services', 'Licence', 'Centre-ville', 'Quinze ans d’expérience en administration locale et en gestion des services publics.', ['Gestion publique', 'Planification'], 'valide',
                [['Secrétaire générale', 'Mairie', '2016-03-01', null], ['Chef de service', 'Préfecture', '2008-01-01', '2016-02-01']]],
            ['Agbodjan', 'Codjo', 'Ingénieur en génie civil', 'Cadres techniques', 'BTP', 'Master', 'Zone industrielle', 'Conducteur de travaux, spécialiste des routes et des ouvrages d’assainissement.', ['Topographie', 'Suivi de chantier'], 'valide',
                [['Ingénieur travaux', 'Société de BTP', '2014-06-01', null]]],
            ['Soglo', 'Fatou', 'Directrice de PME agroalimentaire', 'Chefs d’entreprise', 'Agriculture', 'Licence', 'Zone industrielle', 'Transformation de manioc et de produits locaux, douze employés.', ['Management', 'Export'], 'valide',
                [['Directrice', 'PME Soglo Alimentaire', '2012-01-01', null]]],
            ['Dossou', 'Rodrigue', 'Menuisier-ébéniste', 'Artisans', 'Artisanat', 'BEPC / CAP', 'Quartier Nord', 'Mobilier sur mesure et formation d’apprentis depuis 2008.', ['Menuiserie', 'Formation'], 'valide', []],
            ['Akpovi', 'Estelle', 'Commerçante en textile', 'Commerçants', 'Commerce', 'Baccalauréat', 'Centre-ville', 'Vente de pagnes et de tissus, réseau de revendeuses dans la commune.', ['Vente', 'Négociation'], 'valide', []],
            ['Tokpanou', 'Yves', 'Fondateur de startup mobile', 'Jeunes entrepreneurs', 'Numérique', 'Licence', 'Centre-ville', 'Applications de paiement et de livraison pour les petits commerces.', ['Développement web', 'Gestion de produit'], 'valide',
                [['Fondateur', 'Startup mobile', '2022-01-01', null], ['Développeur', 'Agence numérique', '2019-01-01', '2021-12-01']]],
            ['Chabi', 'Mariam', 'Investisseuse immobilière', 'Investisseurs / partenaires', 'Finance', 'Master', 'Centre-ville', 'Financement de logements abordables et de marchés locaux.', ['Financement', 'Immobilier'], 'valide', []],
            ['Gnonlonfoun', 'Sylvain', 'Médecin chef de centre de santé', 'Cadres administratifs', 'Santé', 'Doctorat', 'Quartier Sud', 'Santé communautaire, prévention et santé maternelle.', ['Santé publique', 'Management'], 'valide', []],
            ['Zinsou', 'Pascaline', 'Couturière-styliste', 'Artisans', 'Artisanat', 'BEPC / CAP', 'Quartier Sud', 'Atelier de confection, collections de mode africaine.', ['Couture', 'Design'], 'valide', []],
            ['Kpadonou', 'Armand', 'Technicien réseaux et énergie solaire', 'Cadres techniques', 'Numérique', 'Baccalauréat', 'Zone industrielle', 'Installation de kits solaires et de réseaux Wi-Fi communautaires.', ['Énergie solaire', 'Réseaux'], 'valide', []],
            ['Biaou', 'Nadège', 'Gérante d’entreprise de transport', 'Chefs d’entreprise', 'Transport', 'Baccalauréat', 'Zone industrielle', 'Flotte de huit minibus reliant les villages au centre-ville.', ['Logistique', 'Gestion de flotte'], 'valide', []],
            ['Adjanohoun', 'Brice', 'Enseignant-chercheur en agronomie', 'Cadres techniques', 'Éducation', 'Doctorat', 'Quartier Nord', 'Recherche sur les cultures vivrières et accompagnement des coopératives.', ['Agronomie', 'Formation'], 'valide', []],
            ['Hounkpatin', 'Léonce', 'Maçon et entrepreneur en bâtiment', 'Artisans', 'BTP', 'BEPC / CAP', 'Village périphérique', 'Construction de maisons et de petits ouvrages, équipe de dix personnes.', ['Maçonnerie', 'Devis'], 'valide', []],
            ['Dansou', 'Rosine', 'Grossiste en produits vivriers', 'Commerçants', 'Commerce', 'Sans diplôme', 'Quartier Sud', 'Approvisionnement des marchés en céréales et en tubercules.', ['Négociation', 'Stockage'], 'valide', []],
            ['Aguessy', 'Mathieu', 'Comptable agréé', 'Cadres administratifs', 'Finance', 'Master', 'Centre-ville', 'Tenue de comptabilité et conseil fiscal pour les PME locales.', ['Comptabilité', 'Fiscalité'], 'valide', []],
            ['Sodjinou', 'Clarisse', 'Fondatrice d’une coopérative de transformation', 'Jeunes entrepreneurs', 'Agriculture', 'Licence', 'Village périphérique', 'Coopérative de femmes transformant le karité et le soja.', ['Coopérative', 'Transformation'], 'valide', []],
            ['Tchibozo', 'Gilles', 'Gestionnaire de fonds d’investissement', 'Investisseurs / partenaires', 'Finance', 'Master', 'Centre-ville', 'Accompagnement de projets agricoles et artisanaux à fort potentiel.', ['Évaluation de projets', 'Financement'], 'valide', []],
            ['Ahouansou', 'Thierry', 'Maraîcher biologique', 'Jeunes entrepreneurs', 'Agriculture', 'Licence', 'Village périphérique', 'Maraîchage biologique, vente directe aux restaurants.', ['Maraîchage', 'Logistique'], 'en_attente', []],
            ['Gbaguidi', 'Sandrine', 'Informaticienne indépendante', 'Cadres techniques', 'Numérique', 'Licence', 'Quartier Nord', 'Création de sites web et maintenance informatique pour les entreprises.', ['Développement web', 'Maintenance'], 'en_attente', []],
            ['Adanhoume', 'Prosper', 'Commerçant en quincaillerie', 'Commerçants', 'Commerce', 'BEPC / CAP', 'Quartier Sud', 'Vente de matériaux et d’outillage pour le bâtiment.', ['Vente', 'Gestion de stock'], 'en_attente', []],
        ];

        // 15 profils supplémentaires générés automatiquement pour obtenir plusieurs pages de résultats
        $noms = ['Kpoton', 'Ahlonsou', 'Lokossou', 'Mensah', 'Fassinou', 'Sagbo', 'Zoumarou', 'Hounnou', 'Amoussou', 'Gandonou', 'Kiki', 'Assogba', 'Toko', 'Yacoubou', 'Bossou'];
        $prenoms = ['Judith', 'Fabrice', 'Reine', 'Ismaël', 'Colette', 'Dieudonné', 'Marcelle', 'Romaric', 'Odile', 'Serge', 'Patricia', 'Désiré', 'Sabine', 'Éric', 'Arlette'];
        $metiers = [
            ['Plombier', 'Artisans', 'BTP', 'BEPC / CAP', ['Plomberie', 'Dépannage']],
            ['Infirmier diplômé d’État', 'Cadres techniques', 'Santé', 'Licence', ['Soins', 'Prévention']],
            ['Chauffeur-livreur indépendant', 'Jeunes entrepreneurs', 'Transport', 'Baccalauréat', ['Logistique', 'Livraison']],
            ['Enseignante de mathématiques', 'Cadres administratifs', 'Éducation', 'Master', ['Pédagogie', 'Formation']],
            ['Électricien du bâtiment', 'Artisans', 'BTP', 'BEPC / CAP', ['Électricité', 'Devis']],
        ];
        $quartiers = ['Centre-ville', 'Quartier Nord', 'Quartier Sud', 'Zone industrielle', 'Village périphérique'];
        foreach (range(0, 14) as $n) {
            [$metier, $cat, $sec, $niv, $comp] = $metiers[$n % 5];
            $donnees[] = [$noms[$n], $prenoms[$n], $metier, $cat, $sec, $niv, $quartiers[$n % 5],
                "Professionnel installé dans la commune, $metier avec plusieurs années d’expérience.", $comp, 'valide', []];
        }

        foreach ($donnees as $i => [$nom, $prenoms, $metier, $cat, $sec, $niv, $quartier, $bio, $competences, $statut, $experiences]) {
            // Un compte par profil. L'index $i dans l'email garantit l'unicité.
            $compte = Utilisateur::firstOrCreate(
                ['email' => strtolower($nom) . $i . '@exemple.local'],
                ['mot_de_passe' => 'Demo-2026-pass', 'role' => 'membre']
            );

            $profil = Profil::firstOrCreate(
                ['utilisateur_id' => $compte->id],
                [
                    'nom' => $nom, 'prenoms' => $prenoms, 'metier' => $metier,
                    'categorie_id' => Categorie::where('libelle', $cat)->value('id'),
                    'secteur_id' => Secteur::where('libelle', $sec)->value('id'),
                    'niveau_etude_id' => NiveauEtude::where('libelle', $niv)->value('id'),
                    'localisation' => $quartier, 'bio' => $bio,
                    'telephone' => sprintf('+229 01 97 %02d %02d %02d', 10 + $i, 20 + $i, 30 + $i),
                    'contact_public' => $i % 3 === 0,       // un profil sur trois montre son téléphone au public
                    'consentement_le' => now(),
                ]
            );
            // « statut » est protégé contre le remplissage en masse : on l'écrit explicitement
            $profil->forceFill(['statut' => $statut])->save();

            $profil->competences()->sync(
                collect($competences)->map(fn ($c) => Competence::firstOrCreate(['libelle' => $c])->id)
            );

            if ($profil->experiences()->count() === 0) {
                foreach ($experiences as [$poste, $organisation, $debut, $fin]) {
                    $profil->experiences()->create(compact('poste', 'organisation', 'debut', 'fin'));
                }
            }
        }
    }
}
