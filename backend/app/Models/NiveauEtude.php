<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Niveau d'étude. Le champ « rang » permet de les trier.
 */
class NiveauEtude extends Model
{
    protected $table = 'niveaux_etude';

    // « guarded » vide sauf l'id : tout le reste peut être rempli en masse
    protected $guarded = ['id'];

    public $timestamps = false;
}
