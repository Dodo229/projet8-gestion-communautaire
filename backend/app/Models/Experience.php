<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Expérience professionnelle d'un profil.
 */
class Experience extends Model
{
    protected $table = 'experiences';

    // « guarded » vide sauf l'id : tout le reste peut être rempli en masse
    protected $guarded = ['id'];

    public $timestamps = false;
    protected $casts = ['debut' => 'date:Y-m-d', 'fin' => 'date:Y-m-d'];
}
