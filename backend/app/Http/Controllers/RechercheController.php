<?php

namespace App\Http\Controllers;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * RÔLE 4 — Jean-Pierre : recherche et consultation des profils.
 *
 * Toutes les routes ici sont PUBLIQUES : un visiteur peut chercher sans compte.
 * Règle de sécurité n°1 : on ne montre JAMAIS un profil qui n'est pas « valide ».
 */
class RechercheController extends Controller
{
    /**
     * La requête de base : les profils VALIDÉS, avec le nom de l'usager et sa catégorie.
     * On utilise des « jointures » (join) pour lire 3 tables en une seule requête.
     */
    private function base(): Builder
    {
        return DB::table('profils')
            ->join('users', 'users.id', '=', 'profils.user_id')
            ->leftJoin('categories', 'categories.id', '=', 'profils.categorie_id')
            ->where('profils.statut', 'valide')
            ->where('users.actif', true)
            ->select(
                'profils.id', 'users.name as nom', 'profils.metier', 'profils.secteur',
                'profils.niveau_etude', 'profils.localisation', 'profils.categorie_id',
                'categories.nom as categorie', 'profils.created_at'
            );
    }

    /**
     * GET /api/profils
     * Exemples : /api/profils?q=menuisier&localisation=calavi&categorie_id=4&tri=recent&page=2
     */
    public function index(Request $r)
    {
        $q = $this->base();

        // 1. Filtres exacts (valeurs choisies dans une liste déroulante)
        foreach (['categorie_id', 'secteur', 'niveau_etude'] as $champ) {
            if ($r->filled($champ)) {               // filled() = présent ET non vide
                $q->where('profils.' . $champ, $r->input($champ));
            }
        }

        // 2. Localisation : recherche partielle (« calavi » trouve « Abomey-Calavi »)
        if ($r->filled('localisation')) {
            $q->where('profils.localisation', 'like', '%' . $this->echapper($r->input('localisation')) . '%');
        }

        // 3. Recherche libre dans le nom, le métier, le secteur, la présentation et les compétences.
        //    Les parenthèses (function ($w) {...}) sont indispensables : sans elles, les OR
        //    contourneraient la règle « statut = valide » (faille de sécurité).
        if ($r->filled('q')) {
            $motif = '%' . $this->echapper(trim($r->input('q'))) . '%';
            $q->where(function ($w) use ($motif) {
                $w->where('users.name', 'like', $motif)
                  ->orWhere('profils.metier', 'like', $motif)
                  ->orWhere('profils.secteur', 'like', $motif)
                  ->orWhere('profils.bio', 'like', $motif)
                  ->orWhere('profils.competences', 'like', $motif);
            });
        }

        // 4. Tri : par nom (par défaut) ou les plus récents d'abord
        if ($r->input('tri') === 'recent') {
            $q->orderByDesc('profils.created_at');
        } else {
            $q->orderBy('users.name');
        }

        // 5. Pagination : 12 profils par page. La réponse contient data[], total, last_page...
        return $q->paginate(12)->withQueryString();
    }

    /**
     * GET /api/profils/filtres
     * Les choix possibles pour les listes déroulantes (seulement ceux qui existent vraiment).
     */
    public function filtres()
    {
        $valides = DB::table('profils')->where('statut', 'valide');

        return response()->json([
            'categories'    => DB::table('categories')->orderBy('nom')->get(['id', 'nom']),
            'secteurs'      => (clone $valides)->distinct()->orderBy('secteur')->pluck('secteur'),
            'niveaux'       => (clone $valides)->distinct()->orderBy('niveau_etude')->pluck('niveau_etude'),
            'localisations' => (clone $valides)->distinct()->orderBy('localisation')->pluck('localisation'),
        ]);
    }

    /**
     * GET /api/profils/{id}
     * Le détail d'un profil. 404 s'il n'existe pas OU s'il n'est pas validé :
     * on ne révèle même pas qu'un profil en attente existe.
     */
    public function show(int $id)
    {
        $profil = $this->base()
            ->addSelect('profils.bio', 'profils.competences', 'profils.experiences', 'profils.telephone')
            ->where('profils.id', $id)
            ->first();

        abort_unless($profil, 404, 'Profil introuvable.');

        return response()->json($profil);
    }

    /** Neutralise les caractères spéciaux de LIKE (% et _) tapés par l'utilisateur. */
    private function echapper(string $texte): string
    {
        return addcslashes($texte, '%_\\');
    }
}
