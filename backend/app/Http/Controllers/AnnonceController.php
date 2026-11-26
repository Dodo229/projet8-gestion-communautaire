<?php

namespace App\Http\Controllers;

use App\Models\Annonce;
use Illuminate\Http\Request;

// Le contrôleur = la logique. La route appelle une de ces fonctions.
class AnnonceController extends Controller
{
    // GET /api/annonces  (public)  -> ?type=annonce ou ?type=actualite (optionnel)
    public function index(Request $request)
    {
        $requete = Annonce::query()->orderByDesc('date_publication');

        if ($request->filled('type')) {
            $requete->where('type', $request->type);
        }

        // paginate(10) : 10 résultats par page, Laravel ajoute les infos de pagination
        return $requete->paginate(10);
    }

    // POST /api/admin/annonces  (admin)
    public function store(Request $request)
    {
        $donnees = $request->validate([
            'titre'            => 'required|string|max:255',
            'contenu'          => 'required|string',
            'type'             => 'required|in:annonce,actualite',
            'date_publication' => 'nullable|date',
        ]);

        // Si l'admin ne donne pas de date, on met maintenant
        $donnees['date_publication'] = $donnees['date_publication'] ?? now();

        $annonce = Annonce::create($donnees);

        return response()->json($annonce, 201); // 201 = "créé"
    }

    // PUT /api/admin/annonces/{annonce}  (admin)
    public function update(Request $request, Annonce $annonce)
    {
        $donnees = $request->validate([
            'titre'            => 'required|string|max:255',
            'contenu'          => 'required|string',
            'type'             => 'required|in:annonce,actualite',
            'date_publication' => 'nullable|date',
        ]);

        $annonce->update($donnees);

        return response()->json($annonce);
    }

    // DELETE /api/admin/annonces/{annonce}  (admin)
    public function destroy(Annonce $annonce)
    {
        $annonce->delete();

        return response()->json(['message' => 'Annonce supprimée']);
    }
}
