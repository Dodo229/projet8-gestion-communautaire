<?php

namespace Tests\Feature;

use App\Mail\ProfilValideMail;
use App\Models\Document;
use App\Models\JournalAdmin;
use App\Models\Profil;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilTest extends TestCase
{
    use RefreshDatabase;

    private function creerCategorie(): int
    {
        return DB::table('categories')->insertGetId([
            'nom'        => 'Artisans',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function creerUsager(string $email = 'usager@exemple.bj'): User
    {
        return User::factory()->create([
            'email' => $email,
        ]);
    }

    private function creerAdmin(): User
    {
        $admin = User::factory()->create(['email' => 'admin@mairie.bj']);
        $admin->forceFill(['role' => 'admin', 'actif' => true])->save();

        return $admin;
    }

    public function test_usager_peut_creer_et_modifier_son_profil_en_attente(): void
    {
        $categorieId = $this->creerCategorie();
        $usager = $this->creerUsager();
        $this->actingAs($usager, 'sanctum');

        $creation = $this->postJson('/api/mon-profil', [
            'categorie_id' => $categorieId,
            'secteur'      => 'Artisanat',
            'metier'       => 'Menuisier',
            'niveau_etude' => 'CAP',
            'localisation' => 'Abomey-Calavi',
            'bio'          => 'Menuisier depuis 8 ans.',
            'competences'  => 'Ébénisterie, pose',
            'experiences'  => '8 ans en atelier',
            'telephone'    => '0197000000',
            'statut'       => 'valide', // doit être ignoré côté serveur
        ]);

        $creation->assertCreated()
            ->assertJsonPath('profil.metier', 'Menuisier')
            ->assertJsonPath('profil.statut', 'en_attente');

        $this->postJson('/api/mon-profil', [
            'secteur'      => 'Artisanat',
            'metier'       => 'Menuisier',
            'niveau_etude' => 'CAP',
            'localisation' => 'Cotonou',
        ])->assertStatus(409);

        $modification = $this->putJson('/api/mon-profil', [
            'categorie_id' => $categorieId,
            'secteur'      => 'Artisanat',
            'metier'       => 'Menuisier-Ébéniste',
            'niveau_etude' => 'BAC',
            'localisation' => 'Cotonou',
        ]);

        $modification->assertOk()
            ->assertJsonPath('profil.metier', 'Menuisier-Ébéniste')
            ->assertJsonPath('profil.localisation', 'Cotonou')
            ->assertJsonPath('profil.statut', 'en_attente');
    }

    public function test_upload_documents_valide_type_et_taille(): void
    {
        Storage::fake('public');
        $usager = $this->creerUsager();
        $this->actingAs($usager, 'sanctum');

        $this->postJson('/api/mon-profil', [
            'secteur'      => 'Numérique',
            'metier'       => 'Développeur web',
            'niveau_etude' => 'Licence',
            'localisation' => 'Cotonou',
        ])->assertCreated();

        // CV PDF valide
        $cv = UploadedFile::fake()->create('cv.pdf', 500, 'application/pdf');
        $this->postJson('/api/mon-profil/documents', [
            'type'    => 'cv',
            'fichier' => $cv,
        ])->assertCreated()->assertJsonPath('document.type', 'cv');

        // Photo JPG valide (pas besoin de GD : create avec mime image/jpeg)
        $photo = UploadedFile::fake()->create('photo.jpg', 300, 'image/jpeg');
        $this->postJson('/api/mon-profil/documents', [
            'type'    => 'photo',
            'fichier' => $photo,
        ])->assertCreated()->assertJsonPath('document.type', 'photo');

        // Fichier .exe refusé
        $exe = UploadedFile::fake()->create('virus.exe', 100, 'application/x-msdownload');
        $this->postJson('/api/mon-profil/documents', [
            'type'    => 'cv',
            'fichier' => $exe,
        ])->assertStatus(422);

        // Fichier trop lourd (> 5 Mo pour un CV) refusé
        $grosCv = UploadedFile::fake()->create('gros.pdf', 6000, 'application/pdf');
        $this->postJson('/api/mon-profil/documents', [
            'type'    => 'cv',
            'fichier' => $grosCv,
        ])->assertStatus(422);

        $this->assertSame(2, Document::count());
    }

    public function test_usager_normal_refuse_sur_routes_admin(): void
    {
        $usager = $this->creerUsager();
        $this->actingAs($usager, 'sanctum');

        $this->getJson('/api/admin/profils')->assertStatus(403);
        $this->patchJson('/api/admin/profils/1/valider')->assertStatus(403);
        $this->patchJson('/api/admin/profils/1/rejeter')->assertStatus(403);
    }

    public function test_admin_valide_et_rejette_profil_avec_journal_et_email(): void
    {
        Mail::fake();

        $usager = $this->creerUsager('artisan@exemple.bj');
        $profil = new Profil([
            'secteur'      => 'Artisanat',
            'metier'       => 'Menuisier',
            'niveau_etude' => 'CAP',
            'localisation' => 'Abomey-Calavi',
        ]);
        $profil->user_id = $usager->id;
        $profil->statut = 'en_attente';
        $profil->save();

        $admin = $this->creerAdmin();
        $this->actingAs($admin, 'sanctum');

        $this->getJson('/api/admin/profils?statut=en_attente')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->patchJson("/api/admin/profils/{$profil->id}/valider")
            ->assertOk()
            ->assertJsonPath('profil.statut', 'valide');

        Mail::assertSent(ProfilValideMail::class, function ($mail) use ($usager) {
            return $mail->hasTo($usager->email);
        });

        $this->patchJson("/api/admin/profils/{$profil->id}/rejeter")
            ->assertOk()
            ->assertJsonPath('profil.statut', 'rejete');

        $this->assertSame(2, JournalAdmin::count());
    }
}
