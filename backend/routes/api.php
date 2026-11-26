<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfilController;
use App\Http\Controllers\Api\ReferenceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes du prototype (préfixe automatique /api, puis /v1)
|--------------------------------------------------------------------------
| FOURNI PAR LE MEMBRE 1 : le fichier complet du projet contient toutes les routes.
| Seules les routes utiles à votre partie sont conservées ici.
*/

Route::prefix('v1')->group(function () {

    // ------------------------------ VOTRE PARTIE (membre 4) ------------------------------
    Route::get('profils', [ProfilController::class, 'index']);                  // liste filtrée
    Route::get('profils/{profil}', [ProfilController::class, 'show']);          // détail
    Route::get('profils/{profil}/photo', [ProfilController::class, 'photo']);   // photo

    // ------------------------------ Fournies par les autres membres ------------------------------
    Route::get('references', ReferenceController::class);                       // membre 5 : listes de choix

    Route::post('connexion', [AuthController::class, 'login'])->middleware('throttle:5,1');   // membre 2
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('deconnexion', [AuthController::class, 'logout']);
        Route::get('moi', [AuthController::class, 'moi']);
    });
});
