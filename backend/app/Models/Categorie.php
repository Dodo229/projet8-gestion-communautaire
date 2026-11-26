<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Catégorie d'acteur économique (ex. Artisans, Commerçants).
 */
class Categorie extends Model
{
    protected $table = 'categories';

    // « guarded » vide sauf l'id : tout le reste peut être rempli en masse
    protected $guarded = ['id'];

    // Cette table n'a pas de colonnes created_at / updated_at
    public $timestamps = false;
}
