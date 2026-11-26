<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * FOURNI PAR LE MEMBRE 2 (comptes et sécurité) : copie pour pouvoir tester la visibilité du téléphone.
 * Connexion, déconnexion et identité de l’utilisateur courant.
 *
 * L'authentification repose sur une SESSION par cookie (Laravel Sanctum en mode
 * « SPA »). Le mot de passe n'est jamais stocké en clair : Laravel compare le
 * hash enregistré en base avec celui du mot de passe saisi.
 */
class AuthController extends Controller
{
    public function login(Request $r)
    {
        $c = $r->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // attempt() cherche l'utilisateur par email, vérifie le mot de passe et que le compte est actif
        if (! Auth::attempt(['email' => $c['email'], 'password' => $c['password'], 'actif' => true])) {
            // Message volontairement vague : on ne révèle pas si c'est l'email ou le mot de passe qui est faux
            return response()->json(['message' => 'Identifiants invalides ou compte suspendu.'], 422);
        }

        // Renouvelle l'identifiant de session après connexion (protection contre la fixation de session)
        $r->session()->regenerate();

        return $this->moi($r);
    }

    public function logout(Request $r)
    {
        Auth::guard('web')->logout();
        $r->session()->invalidate();          // détruit la session
        $r->session()->regenerateToken();     // nouveau jeton CSRF

        return response()->noContent();       // réponse 204 : succès sans contenu
    }

    /** Renvoie l'identité du compte connecté (appelée au chargement du site React). */
    public function moi(Request $r)
    {
        $u = $r->user();

        return [
            'id' => $u->id,
            'email' => $u->email,
            'role' => $u->role,
            'profil_id' => $u->profil?->id,
        ];
    }
}
