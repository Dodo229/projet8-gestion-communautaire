<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

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