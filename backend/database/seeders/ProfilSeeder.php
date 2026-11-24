<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProfilSeeder extends Seeder
{
    public function run(): void
    {
        $prenoms = ['Kossi', 'Afi', 'Sèna', 'Rodrigue', 'Mariam', 'Fifamè', 'Koffi', 'Aïcha',
                    'Gildas', 'Nadège', 'Ulrich', 'Bénédicte', 'Romaric', 'Grâce', 'Arnaud'];
        $noms = ['Houngbo', 'Adjovi', 'Agossou', 'Zinsou', 'Tossou', 'Akpovi',
                 'Hounkpè', 'Sossou', 'Azonhiho', 'Dansou'];
        $villes = ['Abomey-Calavi', 'Cotonou', 'Porto-Novo', 'Ouidah', 'Allada',
                   'Bohicon', 'Parakou', 'Lokossa'];
        $niveaux = ['CAP', 'BEPC', 'BAC', 'Licence', 'Master', 'Doctorat'];

        // Pour chaque catégorie : un secteur et 3 métiers possibles
        $metiersParCategorie = [
            'Cadres administratifs' => ['Administration publique', ['Secrétaire administratif', 'Gestionnaire RH', 'Comptable']],
            'Cadres techniques' => ['Ingénierie', ['Ingénieur génie civil', 'Technicien informatique', 'Électricien industriel']],
            'Chefs d\'entreprise' => ['Commerce et services', ['Directeur d\'entreprise', 'Gérant de PME', 'Entrepreneur BTP']],
            'Artisans' => ['Artisanat', ['Menuisier', 'Couturière', 'Soudeur']],
            'Commerçants' => ['Commerce', ['Commerçant de tissus', 'Vendeur de matériaux', 'Grossiste alimentaire']],
            'Jeunes entrepreneurs' => ['Numérique', ['Développeur web', 'Graphiste freelance', 'Community manager']],
            'Investisseurs / partenaires' => ['Finance', ['Investisseur agricole', 'Partenaire financier', 'Consultant en investissement']],
        ];

        $categories = DB::table('categories')->pluck('id', 'nom'); // ex. ['Artisans' => 4, ...]
        $motDePasse = Hash::make('password'); // le même pour tous les faux comptes

        for ($i = 1; $i <= 30; $i++) {
            $prenom = $prenoms[array_rand($prenoms)];
            $nom = $noms[array_rand($noms)];

            // 1. Le compte utilisateur
            $userId = DB::table('users')->insertGetId([
                'name' => "$prenom $nom",
                'email' => "profil$i@exemple.bj",
                'password' => $motDePasse,
                'role' => 'usager',
                'actif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Son profil, dans une catégorie au hasard
            $nomCategorie = array_rand($metiersParCategorie);
            [$secteur, $metiers] = $metiersParCategorie[$nomCategorie];
            $metier = $metiers[array_rand($metiers)];

            // 24 profils validés, 3 en attente, 3 rejetés (pour tester tous les cas)
            $statut = $i <= 24 ? 'valide' : ($i <= 27 ? 'en_attente' : 'rejete');

            DB::table('profils')->insert([
                'user_id' => $userId,
                'categorie_id' => $categories[$nomCategorie],
                'secteur' => $secteur,
                'metier' => $metier,
                'niveau_etude' => $niveaux[array_rand($niveaux)],
                'localisation' => $villes[array_rand($villes)],
                'bio' => "$metier basé(e) au Bénin, membre actif de la communauté économique locale.",
                'competences' => 'Travail en équipe, rigueur, sens du service',
                'experiences' => rand(1, 15) . ' ans d\'expérience',
                'telephone' => '01' . rand(10000000, 99999999),
                'statut' => $statut,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}