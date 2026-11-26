<?php

namespace App\Http\Controllers;

use App\Models\AbonneNewsletter;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    // POST /api/newsletter  (public)
    public function store(Request $request)
    {
        $donnees = $request->validate([
            // unique: refuse un e-mail déjà inscrit
            'email' => 'required|email|max:255|unique:abonnes_newsletter,email',
        ]);

        AbonneNewsletter::create($donnees);

        return response()->json(['message' => 'Merci, vous êtes abonné !'], 201);
    }
}
