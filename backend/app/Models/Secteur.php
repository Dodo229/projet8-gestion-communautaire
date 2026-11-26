<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Secteur d'activité (ex. Agriculture, Numérique).
 */
class Secteur extends Model
{
    protected $table = 'secteurs';

    // « guarded » vide sauf l'id : tout le reste peut être rempli en masse
    protected $guarded = ['id'];

    public $timestamps = false;
}
