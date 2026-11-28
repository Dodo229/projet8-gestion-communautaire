<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * FOURNI PAR LE MEMBRE 1 : listes de choix, puis profils de démonstration.
 * Lancé par « php artisan migrate --seed » ou « php artisan db:seed ».
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategorieSeeder::class, // d'abord les catégories
            AdminSeeder::class,     // puis le compte admin
            ProfilSeeder::class,    // puis les 30 profils (ils ont besoin des catégories)
        ]);
    }
}