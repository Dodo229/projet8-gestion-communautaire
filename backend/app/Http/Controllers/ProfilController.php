<?php

namespace App\Http\Controllers;

use App\Mail\ProfilValideMail;
use App\Models\Document;
use App\Models\Profil;
use App\Support\Journal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ProfilController extends Controller
{
    /**
     * GET /api/mon-profil — Récupère le profil de l'usager connecté et la liste des catégories.
     */
    public function showMine(Request $request): JsonResponse
    {
        $profil = Profil::with(['user:id,name,email', 'documents'])
            ->where('user_id', $request->user()->id)
            ->first();

        $categories = DB::table('categories')
            ->select('id', 'nom')
            ->orderBy('id')
            ->get();

        return response()->json([
            'profil'     => $profil ? $this->formatProfil($profil) : null,
            'categories' => $categories,
        ]);
    }

    /**
     * POST /api/mon-profil — Crée le profil de l'usager connecté (statut initial : en_attente).
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (Profil::where('user_id', $user->id)->exists()) {
            return response()->json([
                'message' => 'Vous avez déjà un profil. Utilisez la modification.',
            ], 409);
        }

        $donnees = $this->validateProfilData($request);

        $profil = new Profil($donnees);
        $profil->user_id = $user->id;
        $profil->statut = 'en_attente';
        $profil->save();

        $profil->load(['user:id,name,email', 'documents']);

        return response()->json([
            'message' => 'Profil créé avec succès. Il est en attente de validation.',
            'profil'  => $this->formatProfil($profil),
        ], 201);
    }

    /**
     * PUT /api/mon-profil — Met à jour uniquement le profil de l'usager connecté.
     */
    public function update(Request $request): JsonResponse
    {
        $profil = Profil::where('user_id', $request->user()->id)->first();

        if (! $profil) {
            return response()->json([
                'message' => 'Aucun profil trouvé pour votre compte.',
            ], 404);
        }

        $donnees = $this->validateProfilData($request);
        $profil->fill($donnees);
        $profil->statut = 'en_attente';
        $profil->save();

        $profil->load(['user:id,name,email', 'documents']);

        return response()->json([
            'message' => 'Profil mis à jour. Il repasse en attente de validation.',
            'profil'  => $this->formatProfil($profil),
        ]);
    }

    /**
     * POST /api/mon-profil/documents — Envoie un document (cv, photo ou legal) pour le profil connecté.
     */
    public function uploadDocument(Request $request): JsonResponse
    {
        $profil = Profil::where('user_id', $request->user()->id)->first();

        if (! $profil) {
            return response()->json([
                'message' => 'Créez d\'abord votre profil avant d\'envoyer des documents.',
            ], 404);
        }

        $request->validate([
            'type' => 'required|in:cv,photo,legal',
        ]);

        $type = $request->input('type');

        $regleFichier = match ($type) {
            'photo' => 'required|file|mimes:jpg,jpeg,png|max:2048',
            'cv'    => 'required|file|mimes:pdf|max:5120',
            'legal' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        };

        $request->validate([
            'fichier' => $regleFichier,
        ]);

        $ancien = Document::where('profil_id', $profil->id)
            ->where('type', $type)
            ->first();

        $chemin = $request->file('fichier')->store("documents/{$profil->id}", 'public');

        if ($ancien) {
            Storage::disk('public')->delete($ancien->chemin_fichier);
            $ancien->update(['chemin_fichier' => $chemin]);
            $document = $ancien;
        } else {
            $document = Document::create([
                'profil_id'      => $profil->id,
                'type'           => $type,
                'chemin_fichier' => $chemin,
            ]);
        }

        return response()->json([
            'message'  => 'Document enregistré avec succès.',
            'document' => $this->formatDocument($document),
        ], 201);
    }

    /**
     * GET /api/admin/profils — Liste les profils pour l'admin (filtrable par ?statut=...).
     */
    public function adminIndex(Request $request): JsonResponse
    {
        if ($erreur = $this->ensureAdmin($request)) {
            return $erreur;
        }

        $requete = Profil::with(['user:id,name,email', 'documents'])->latest();

        $statut = $request->query('statut');
        if ($statut && $statut !== 'tous' && in_array($statut, ['en_attente', 'valide', 'rejete'], true)) {
            $requete->where('statut', $statut);
        }

        $profils = $requete->get()->map(fn (Profil $p) => $this->formatProfil($p));

        return response()->json([
            'data' => $profils,
        ]);
    }

    /**
     * PATCH /api/admin/profils/{id}/valider — Valide un profil, journalise l'action et notifie l'usager.
     */
    public function valider(Request $request, int $id): JsonResponse
    {
        if ($erreur = $this->ensureAdmin($request)) {
            return $erreur;
        }

        $profil = Profil::with(['user:id,name,email', 'documents'])->findOrFail($id);
        $profil->statut = 'valide';
        $profil->save();

        Journal::journaliser(
            'Validation de profil',
            "Profil #{$profil->id} ({$profil->metier}) de {$profil->user?->name} validé"
        );

        if ($profil->user?->email) {
            try {
                Mail::to($profil->user->email)->send(new ProfilValideMail($profil->user->name));
            } catch (\Throwable $e) {
                Log::warning('Envoi e-mail validation profil échoué : '.$e->getMessage());
            }
        }

        return response()->json([
            'message' => 'Profil validé avec succès.',
            'profil'  => $this->formatProfil($profil),
        ]);
    }

    /**
     * PATCH /api/admin/profils/{id}/rejeter — Rejette un profil et journalise l'action.
     */
    public function rejeter(Request $request, int $id): JsonResponse
    {
        if ($erreur = $this->ensureAdmin($request)) {
            return $erreur;
        }

        $profil = Profil::with(['user:id,name,email', 'documents'])->findOrFail($id);
        $profil->statut = 'rejete';
        $profil->save();

        Journal::journaliser(
            'Rejet de profil',
            "Profil #{$profil->id} ({$profil->metier}) de {$profil->user?->name} rejeté"
        );

        return response()->json([
            'message' => 'Profil rejeté.',
            'profil'  => $this->formatProfil($profil),
        ]);
    }

    private function validateProfilData(Request $request): array
    {
        return $request->validate([
            'categorie_id' => 'nullable|integer|exists:categories,id',
            'secteur'      => 'required|string|max:120',
            'metier'       => 'required|string|max:120',
            'niveau_etude' => 'required|string|max:80',
            'localisation' => 'required|string|max:120',
            'bio'          => 'nullable|string|max:2000',
            'competences'  => 'nullable|string|max:1000',
            'experiences'  => 'nullable|string|max:2000',
            'telephone'    => 'nullable|string|max:30',
        ]);
    }

    private function ensureAdmin(Request $request): ?JsonResponse
    {
        if ($request->user()?->role !== 'admin') {
            return response()->json([
                'message' => 'Accès réservé aux administrateurs.',
            ], 403);
        }

        return null;
    }

    private function formatProfil(Profil $profil): array
    {
        $categorieNom = null;
        if ($profil->categorie_id) {
            $categorieNom = DB::table('categories')
                ->where('id', $profil->categorie_id)
                ->value('nom');
        }

        return [
            'id'            => $profil->id,
            'user_id'       => $profil->user_id,
            'categorie_id'  => $profil->categorie_id,
            'categorie_nom' => $categorieNom,
            'secteur'       => $profil->secteur,
            'metier'        => $profil->metier,
            'niveau_etude'  => $profil->niveau_etude,
            'localisation'  => $profil->localisation,
            'bio'           => $profil->bio,
            'competences'   => $profil->competences,
            'experiences'   => $profil->experiences,
            'telephone'     => $profil->telephone,
            'statut'        => $profil->statut,
            'user'          => $profil->user ? [
                'id'    => $profil->user->id,
                'name'  => $profil->user->name,
                'email' => $profil->user->email,
            ] : null,
            'documents'     => $profil->documents->map(fn (Document $d) => $this->formatDocument($d))->values(),
            'created_at'    => $profil->created_at,
            'updated_at'    => $profil->updated_at,
        ];
    }

    private function formatDocument(Document $document): array
    {
        return [
            'id'             => $document->id,
            'type'           => $document->type,
            'chemin_fichier' => $document->chemin_fichier,
            'url'            => Storage::disk('public')->url($document->chemin_fichier),
        ];
    }
}
