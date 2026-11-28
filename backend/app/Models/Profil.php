<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profil extends Model
{
    protected $table = 'profils';

    // user_id et statut sont exclus volontairement pour éviter toute modification non autorisée
    protected $fillable = [
        'categorie_id',
        'secteur',
        'metier',
        'niveau_etude',
        'localisation',
        'bio',
        'competences',
        'experiences',
        'telephone',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(Categorie::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
