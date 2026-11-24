<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorieSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['Cadres administratifs', 'Agents et cadres de l\'administration publique et privée'],
            ['Cadres techniques', 'Ingénieurs, techniciens et experts techniques'],
            ['Chefs d\'entreprise', 'Dirigeants et gérants d\'entreprises de la commune'],
            ['Artisans', 'Menuisiers, couturiers, soudeurs et autres métiers manuels'],
            ['Commerçants', 'Commerçants, grossistes et détaillants'],
            ['Jeunes entrepreneurs', 'Porteurs de projets et jeunes créateurs d\'entreprise'],
            ['Investisseurs / partenaires', 'Investisseurs et partenaires financiers ou techniques'],
        ];

        foreach ($categories as [$nom, $description]) {
            DB::table('categories')->insert([
                'nom' => $nom,
                'description' => $description,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}