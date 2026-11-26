<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbonneNewsletter extends Model
{
    protected $table = 'abonnes_newsletter';

    protected $fillable = ['email'];
}
