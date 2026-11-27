<?php

namespace App\Http\Controllers;

use App\Models\Profil;
use Illuminate\Http\Request;

class MonCompteController extends Controller
{
    public function donnees(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'compte' => $user->only('id', 'name', 'email', 'role', 'actif', 'created_at'),
            'profil' => Profil::where('user_id', $user->id)->first(),
        ]);
    }

    public function destroy(Request $request)
    {
        $request->user()->delete();

        return response()->json(['message' => 'Compte supprimé']);
    }
}