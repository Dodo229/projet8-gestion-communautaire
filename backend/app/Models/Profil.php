<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Profil d'un cadre ou d'un opérateur économique.
 *
 * PARTIE MEMBRE 4 : la méthode toPublic() (ce qu'un visiteur a le droit de voir)
 * et le scope valide() (règle RG1). Le reste du modèle est partagé avec l'équipe.
 */
class Profil extends Model
{
    protected $table = 'profils';

    // Le statut ne peut pas être modifié par remplissage en masse : seul le code serveur (admin) le change
    protected $guarded = ['id', 'statut', 'motif_rejet'];

    protected $casts = ['contact_public' => 'boolean', 'consentement_le' => 'datetime'];

    // ---------- Relations : chaque méthode correspond à une jointure SQL ----------
    public function utilisateur(): BelongsTo { return $this->belongsTo(Utilisateur::class); }
    public function categorie(): BelongsTo { return $this->belongsTo(Categorie::class); }
    public function secteur(): BelongsTo { return $this->belongsTo(Secteur::class); }
    public function niveau(): BelongsTo { return $this->belongsTo(NiveauEtude::class, 'niveau_etude_id'); }
    public function competences(): BelongsToMany { return $this->belongsToMany(Competence::class, 'profil_competence'); }
    public function experiences(): HasMany { return $this->hasMany(Experience::class)->orderByDesc('debut'); }

    /**
     * Scope : Profil::valide()->get() équivaut à « WHERE statut = 'valide' ».
     * Règle de gestion RG1 : seuls les profils validés apparaissent dans l'annuaire.
     */
    public function scopeValide(Builder $q): Builder
    {
        return $q->where('statut', 'valide');
    }

    /**
     * Version « publique » du profil, prête à être transformée en JSON.
     *
     * On construit le tableau champ par champ au lieu de renvoyer le modèle entier :
     * ainsi, une colonne ajoutée plus tard à la table (email, document...) ne fuit jamais
     * par accident dans l'annuaire. C'est une mesure de protection des données.
     *
     * @param bool $connecte  true si le demandeur est connecté : il voit alors le téléphone (RG6)
     */
    public function toPublic(bool $connecte = false): array
    {
        // loadMissing : charge les relations SEULEMENT si elles ne sont pas déjà chargées
        $this->loadMissing(['categorie', 'secteur', 'niveau', 'competences', 'experiences']);

        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'prenoms' => $this->prenoms,
            'metier' => $this->metier,
            'entreprise' => $this->entreprise,
            'localisation' => $this->localisation,
            'bio' => $this->bio,
            'a_photo' => $this->photo_chemin !== null,
            'categorie' => $this->categorie?->libelle,
            'secteur' => $this->secteur?->libelle,
            'niveau_etude' => $this->niveau?->libelle,
            'competences' => $this->competences->pluck('libelle'),
            'experiences' => $this->experiences,
            // RG6 : visible si le membre l'a autorisé OU si le visiteur est connecté
            'telephone' => ($this->contact_public || $connecte) ? $this->telephone : null,
            'statut' => $this->statut,
        ];
    }
}
