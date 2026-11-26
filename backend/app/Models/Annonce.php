<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Un "modèle" = la table `annonces` vue par Laravel.
class Annonce extends Model
{
    protected $table = 'annonces';

    // Les colonnes que l'on a le droit de remplir via create() / update()
    protected $fillable = ['titre', 'contenu', 'type', 'date_publication'];
}
