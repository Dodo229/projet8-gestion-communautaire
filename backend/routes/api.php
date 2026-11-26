<?php
use App\Http\Controllers\AnnonceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ROUTES DE L'API — FICHIER PARTAGÉ PAR LES 8 MEMBRES
|--------------------------------------------------------------------------
| Règle : chacun écrit UNIQUEMENT dans SA section, entre ses deux lignes ====.
| Laravel ajoute /api devant automatiquement : Route::get('/profils') = /api/profils
|
| 3 types de routes :
|   - publique (tout le monde) :  Route::get('/adresse', ...);
|   - usager connecté :           Route::get('/adresse', ...)->middleware('auth:sanctum');
|   - admin seulement :           Route::get('/admin/adresse', ...)->middleware(['auth:sanctum', 'admin']);
|
| Les lignes "use App\Http\Controllers\MonController;" se mettent
| au début de SA section, avant ses routes.
*/


// ==================== RÔLE 2 — Oremus : inscription et connexion ====================


// ==================== FIN RÔLE 2 ====================


// ==================== RÔLE 3 — Eckson : profils ====================


// ==================== FIN RÔLE 3 ====================


// ==================== RÔLE 4 — Jean-Pierre : recherche et consultation ====================


// ==================== FIN RÔLE 4 ====================


// ==================== RÔLE 5 — Mia : annuaire et catégories ====================


// ==================== FIN RÔLE 5 ====================


// ==================== RÔLE 6 — Casimir : tableau de bord et statistiques ====================


// ==================== FIN RÔLE 6 ====================


// ==================== RÔLE 7 — Vernon : communication ====================
use App\Http\Controllers\AnnonceController;
use App\Http\Controllers\NewsletterController;

Route::get('/annonces', [AnnonceController::class, 'index']);
Route::post('/newsletter', [NewsletterController::class, 'store'])
    ->middleware('throttle:5,1');

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::post('/annonces', [AnnonceController::class, 'store']);
    Route::put('/annonces/{annonce}', [AnnonceController::class, 'update']);
    Route::delete('/annonces/{annonce}', [AnnonceController::class, 'destroy']);
});

// ==================== FIN RÔLE 7 ====================


// ==================== RÔLE 8 — Eustache : comptes et protection des données ====================


// ==================== FIN RÔLE 8 ====================