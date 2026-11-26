<?php

namespace Database\Seeders;

use App\Models\Categorie;
use App\Models\NiveauEtude;
use App\Models\Secteur;
use Illuminate\Database\Seeder;

/**
 * FOURNI PAR LE MEMBRE 1 : listes de choix, puis profils de démonstration.
 * Lancé par « php artisan migrate --seed » ou « php artisan db:seed ».
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Cadres administratifs', 'Cadres techniques', 'Chefs d’entreprise', 'Artisans',
                  'Commerçants', 'Jeunes entrepreneurs', 'Investisseurs / partenaires'] as $libelle) {
            Categorie::firstOrCreate(['libelle' => $libelle]);
        }
        foreach (['Agriculture', 'Commerce', 'Artisanat', 'Numérique', 'Santé', 'Éducation',
                  'BTP', 'Transport', 'Services', 'Finance'] as $libelle) {
            Secteur::firstOrCreate(['libelle' => $libelle]);
        }
        foreach (['Sans diplôme', 'BEPC / CAP', 'Baccalauréat', 'Licence', 'Master', 'Doctorat'] as $rang => $libelle) {
            NiveauEtude::firstOrCreate(['libelle' => $libelle], ['rang' => $rang]);
        }

        $this->call(DemoSeeder::class);
    }
}
