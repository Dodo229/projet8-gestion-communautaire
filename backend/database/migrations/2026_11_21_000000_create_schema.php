<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| FOURNI PAR LE MEMBRE 1 (chef de projet) : version réduite pour le prototype
|--------------------------------------------------------------------------
| Seules les tables dont votre partie a besoin sont créées ici.
| Ce qui concerne VOTRE travail dans cette migration :
|   - l'index composé « idx_profils_filtre » (accélère les filtres de l'annuaire)
|   - l'index FULLTEXT MySQL (accélère la recherche libre)
*/

return new class extends Migration {
    public function up(): void
    {
        Schema::create('utilisateurs', function (Blueprint $t) {
            $t->id();
            $t->string('email', 190)->unique();
            $t->string('mot_de_passe');
            $t->enum('role', ['membre', 'admin'])->default('membre');
            $t->boolean('actif')->default(true);
            $t->rememberToken();
            $t->timestamps();
        });

        foreach (['categories', 'secteurs', 'competences'] as $table) {
            Schema::create($table, function (Blueprint $t) {
                $t->id();
                $t->string('libelle', 100)->unique();
            });
        }
        Schema::create('niveaux_etude', function (Blueprint $t) {
            $t->id();
            $t->string('libelle', 100)->unique();
            $t->smallInteger('rang');
        });

        Schema::create('profils', function (Blueprint $t) {
            $t->id();
            $t->foreignId('utilisateur_id')->unique()->constrained('utilisateurs')->cascadeOnDelete();
            $t->foreignId('categorie_id')->constrained('categories');
            $t->foreignId('secteur_id')->constrained('secteurs');
            $t->foreignId('niveau_etude_id')->constrained('niveaux_etude');
            $t->string('nom', 100);
            $t->string('prenoms', 150);
            $t->string('metier', 150);
            $t->string('entreprise', 150)->nullable();
            $t->string('localisation', 150);
            $t->string('telephone', 30)->nullable();
            $t->boolean('contact_public')->default(false);
            $t->text('bio')->nullable();
            $t->string('photo_chemin')->nullable();
            $t->enum('statut', ['en_attente', 'valide', 'rejete', 'suspendu'])->default('en_attente');
            $t->text('motif_rejet')->nullable();
            $t->timestamp('consentement_le');
            $t->timestamps();

            // Index composé : MySQL l'utilise quand une requête filtre sur statut + secteur + catégorie
            $t->index(['statut', 'secteur_id', 'categorie_id'], 'idx_profils_filtre');
        });

        // Index FULLTEXT : propre à MySQL / MariaDB (recherche « MATCH ... AGAINST »).
        // On le crée seulement avec ces bases : SQLite (tests) et PostgreSQL ne le supportent pas de cette façon.
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            Schema::table('profils', function (Blueprint $t) {
                // IMPORTANT : la requête MATCH doit citer EXACTEMENT ces colonnes, dans le même ordre
                $t->fullText(['nom', 'prenoms', 'metier', 'bio'], 'ft_profils_recherche');
            });
        }

        Schema::create('profil_competence', function (Blueprint $t) {
            $t->foreignId('profil_id')->constrained('profils')->cascadeOnDelete();
            $t->foreignId('competence_id')->constrained('competences');
            $t->primary(['profil_id', 'competence_id']);
        });

        Schema::create('experiences', function (Blueprint $t) {
            $t->id();
            $t->foreignId('profil_id')->constrained('profils')->cascadeOnDelete();
            $t->string('poste', 150);
            $t->string('organisation', 150)->nullable();
            $t->date('debut')->nullable();
            $t->date('fin')->nullable();
            $t->text('description')->nullable();
        });
    }

    public function down(): void
    {
        foreach (['experiences', 'profil_competence', 'profils', 'competences', 'niveaux_etude', 'secteurs', 'categories', 'utilisateurs'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
