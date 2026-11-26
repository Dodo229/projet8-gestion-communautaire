<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categorie;
use App\Models\NiveauEtude;
use App\Models\Secteur;

/**
 * FOURNI PAR LE MEMBRE 5 (catégories) : copie réduite pour que vos filtres aient leurs listes.
 * Renvoie les listes de choix des menus déroulants de l'annuaire.
 * Controller « invokable » : une seule méthode __invoke, appelée directement par la route.
 */
class ReferenceController extends Controller
{
    public function __invoke()
    {
        return [
            'categories' => Categorie::orderBy('id')->get(),
            'secteurs' => Secteur::orderBy('libelle')->get(),
            'niveaux' => NiveauEtude::orderBy('rang')->get(),
        ];
    }
}
