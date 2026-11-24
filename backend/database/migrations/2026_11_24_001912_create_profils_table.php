<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('profils', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete(); // 1 profil par compte
    $table->foreignId('categorie_id')->nullable()->constrained('categories')->nullOnDelete();
    $table->string('secteur');
    $table->string('metier');
    $table->string('niveau_etude');
    $table->string('localisation');
    $table->text('bio')->nullable();
    $table->text('competences')->nullable();
    $table->text('experiences')->nullable();
    $table->string('telephone')->nullable();
    $table->string('statut')->default('en_attente'); // en_attente, valide ou rejete
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profils');
    }
};
