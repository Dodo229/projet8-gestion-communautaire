<?php

namespace App\Http\Controllers;

use App\Models\Categorie;

class CategorieController extends Controller
{
    public function index()
    {
        $categories = Categorie::withCount([
            'profils' => fn ($requete) => $requete->where('statut', 'valide'),
        ])->get();

        return response()->json($categories);
    }

    public function profils(Categorie $categorie)
    {
        $profils = $categorie->profils()
            ->where('statut', 'valide')
            ->get(['id', 'user_id', 'secteur', 'metier', 'niveau_etude', 'localisation', 'bio']);

        return response()->json($profils);
    }
}
