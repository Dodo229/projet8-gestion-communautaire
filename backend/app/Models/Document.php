<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    protected $table = 'documents';

    protected $fillable = [
        'profil_id',
        'type',
        'chemin_fichier',
    ];

    public function profil(): BelongsTo
    {
        return $this->belongsTo(Profil::class);
    }
}
