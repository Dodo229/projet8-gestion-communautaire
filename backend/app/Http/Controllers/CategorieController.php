<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Support\Journal;
use Illuminate\Http\Request;

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
    public function store(Request $request)
    {
        $donnees = $request->validate([
            'nom' => 'required|string|max:255|unique:categories,nom',
            'description' => 'nullable|string',
        ], [
            'nom.required' => 'Le nom de la catégorie est obligatoire.',
            'nom.unique' => 'Une catégorie porte déjà ce nom.',
        ]);

        $categorie = Categorie::create($donnees);

        Journal::journaliser('Création de la catégorie ' . $categorie->nom);

        return response()->json($categorie, 201);
    }

    public function update(Request $request, Categorie $categorie)
    {
        $donnees = $request->validate([
            'nom' => 'required|string|max:255|unique:categories,nom,' . $categorie->id,
            'description' => 'nullable|string',
        ], [
            'nom.required' => 'Le nom de la catégorie est obligatoire.',
            'nom.unique' => 'Une catégorie porte déjà ce nom.',
        ]);

        $categorie->update($donnees);

        Journal::journaliser('Modification de la catégorie ' . $categorie->nom);

        return response()->json($categorie);
    }

    public function destroy(Categorie $categorie)
    {
        if ($categorie->profils()->exists()) {
            return response()->json([
                'message' => 'Impossible de supprimer cette catégorie : elle contient des profils.',
            ], 409);
        }

        $nom = $categorie->nom;
        $categorie->delete();

        Journal::journaliser('Suppression de la catégorie ' . $nom);

        return response()->json(['message' => 'Catégorie supprimée.']);
    }
      
}
