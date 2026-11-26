<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Compte de connexion (membre ou administrateur).
 *
 * On étend « Authenticatable » : c'est la classe que Laravel utilise pour gérer
 * la connexion. Comme notre table s'appelle « utilisateurs » et que la colonne du
 * mot de passe s'appelle « mot_de_passe » (et non « users » / « password »), on
 * le précise ci-dessous.
 */
class Utilisateur extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'utilisateurs';

    // Colonnes que l'on a le droit de remplir en masse (Utilisateur::create([...]))
    protected $fillable = ['email', 'mot_de_passe', 'role', 'actif'];

    // Colonnes jamais renvoyées dans les réponses JSON (sécurité)
    protected $hidden = ['mot_de_passe', 'remember_token'];

    // Le cast « hashed » hache automatiquement le mot de passe à l'enregistrement (bcrypt)
    protected $casts = ['mot_de_passe' => 'hashed', 'actif' => 'boolean'];

    /** Indique à Laravel dans quelle colonne se trouve le hash du mot de passe. */
    public function getAuthPasswordName(): string
    {
        return 'mot_de_passe';
    }

    /** Un compte possède au plus un profil. */
    public function profil(): HasOne
    {
        return $this->hasOne(Profil::class);
    }

    public function estAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
