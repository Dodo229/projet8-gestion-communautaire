<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Compétence (mot-clé) associée à un ou plusieurs profils.
 */
class Competence extends Model
{
    protected $table = 'competences';

    // « guarded » vide sauf l'id : tout le reste peut être rempli en masse
    protected $guarded = ['id'];

    public $timestamps = false;
}
