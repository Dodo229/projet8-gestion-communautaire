<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profil;
use App\Services\FiltreProfils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * PARTIE MEMBRE 4 : consultation publique des profils.
 *
 * Dans le projet complet, ce contrôleur contient aussi « store » (inscription), qui
 * appartient aux membres 2 et 3. Ici, seules vos trois méthodes sont conservées :
 *   index  -> liste filtrée et paginée
 *   show   -> détail d'un profil
 *   photo  -> image du profil
 */
class ProfilController extends Controller
{
    /**
     * GET /api/v1/profils
     * Exemples : /profils?secteur_id=3&localisation=nord&q=menuisier&tri=recent&page=2
     */
    public function index(Request $r)
    {
        // Profil::valide()  -> WHERE statut = 'valide'  (règle RG1)
        // with([...])       -> « chargement anticipé » : 1 requête par relation au lieu d'1 requête par profil.
        //                      Sans lui, 12 profils déclencheraient 12 x 4 requêtes supplémentaires (problème « N+1 »).
        $requete = Profil::valide()->with(['categorie', 'secteur', 'niveau', 'competences']);

        // On complète la requête avec les filtres de l'adresse
        FiltreProfils::appliquer($requete, $r);

        // paginate(12) : exécute la requête avec LIMIT 12 OFFSET ... et ajoute le nombre total de résultats.
        // withQueryString() : les liens de pagination conservent les filtres.
        $page = $requete->paginate(12)->withQueryString();

        // On remplace chaque profil par sa version publique (sans donnée sensible)
        $connecte = (bool) $r->user();
        $page->getCollection()->transform(fn (Profil $p) => $p->toPublic($connecte));

        // La réponse JSON contient : data[], current_page, last_page, total, per_page...
        return $page;
    }

    /**
     * GET /api/v1/profils/{profil}
     * « Profil $profil » : Laravel retrouve automatiquement le profil dont l'id est dans l'adresse
     * (liaison implicite). Si l'id n'existe pas, il répond tout seul par une erreur 404.
     */
    public function show(Request $r, Profil $profil)
    {
        $u = $r->user();

        // Un profil non validé n'est visible que par son propriétaire et les administrateurs
        $autorise = $profil->statut === 'valide'
            || ($u && ($u->estAdmin() || $u->id === $profil->utilisateur_id));

        // 404 (et non 403) : on ne révèle même pas que ce profil existe
        abort_unless($autorise, 404);

        return $profil->toPublic((bool) $u);
    }

    /**
     * GET /api/v1/profils/{profil}/photo
     * Les photos sont stockées sur un disque PRIVÉ (non accessible directement par une adresse).
     * Cette méthode les lit et les renvoie, seulement si le profil est consultable.
     */
    public function photo(Request $r, Profil $profil)
    {
        $u = $r->user();
        $autorise = $profil->statut === 'valide'
            || ($u && ($u->estAdmin() || $u->id === $profil->utilisateur_id));

        abort_unless($autorise && $profil->photo_chemin, 404);

        return Storage::disk('local')->response($profil->photo_chemin);
    }
}
