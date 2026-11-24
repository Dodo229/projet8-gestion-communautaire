<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            'name' => 'Administrateur Mairie',
            'email' => 'admin@mairie.bj',
            'password' => Hash::make('Admin@2026'), // mot de passe chiffré
            'role' => 'admin',
            'actif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}