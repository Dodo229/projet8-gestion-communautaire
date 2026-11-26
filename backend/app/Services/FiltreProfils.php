<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * PARTIE MEMBRE 4 : le coeur de la recherche.
 *
 * Reçoit une requête SQL « en construction » ($q) et la complète avec les filtres
 * envoyés par le navigateur dans l'adresse (?secteur_id=3&q=menuisier&page=2).
 * Aucune requête n'est exécutée ici : on ne fait qu'AJOUTER des conditions WHERE.
 * L'exécution a lieu plus tard, dans le contrôleur (paginate).
 *
 * Cette classe fonctionne avec MySQL, PostgreSQL et SQLite (tests) : elle détecte la
 * base utilisée et adapte la recherche libre.
 */
class FiltreProfils
{
    public static function appliquer(Builder $q, Request $r): Builder
    {
        // Nom du pilote de base de données : 'mysql', 'mariadb', 'pgsql' ou 'sqlite'
        $pilote = DB::getDriverName();
        $pg = $pilote === 'pgsql';
        $mysql = in_array($pilote, ['mysql', 'mariadb'], true);

        // Opérateur de comparaison partielle : PostgreSQL distingue majuscules et minuscules
        // avec LIKE, il faut donc ILIKE. Avec MySQL, LIKE ignore déjà la casse ET les accents
        // (collation utf8mb4_unicode_ci) : « ingenieur » trouve « Ingénieur ».
        $like = $pg ? 'ilike' : 'like';

        // ---------------------------------------------------------------------------
        // 1. Filtres exacts : un identifiant choisi dans une liste déroulante
        //    Équivalent SQL : WHERE categorie_id = 3 AND secteur_id = 2 ...
        // ---------------------------------------------------------------------------
        foreach (['categorie_id', 'secteur_id', 'niveau_etude_id'] as $colonne) {
            if ($r->filled($colonne)) {            // filled() : présent ET non vide
                $q->where($colonne, $r->input($colonne));
            }
        }

        // ---------------------------------------------------------------------------
        // 2. Localisation : recherche partielle (« nord » trouve « Quartier Nord »)
        // ---------------------------------------------------------------------------
        if ($r->filled('localisation')) {
            $q->where('localisation', $like, '%' . self::echapper($r->input('localisation')) . '%');
        }

        // ---------------------------------------------------------------------------
        // 3. Compétence : « le profil possède cette compétence ».
        //    whereHas génère une sous-requête : WHERE EXISTS (SELECT ... FROM competences ...)
        // ---------------------------------------------------------------------------
        if ($r->filled('competence')) {
            $q->whereHas('competences', function (Builder $c) use ($r) {
                $c->where('libelle', $r->input('competence'));
            });
        }

        // ---------------------------------------------------------------------------
        // 4. Recherche libre (nom, prénoms, métier, présentation)
        // ---------------------------------------------------------------------------
        if ($r->filled('q')) {
            $t = trim($r->input('q'));
            $motif = '%' . self::echapper($t) . '%';

            // Le regroupement (function ($w) { ... }) place les conditions entre parenthèses :
            //   WHERE statut = 'valide' AND ( A OR B OR C )
            // Sans lui, les OR contourneraient le filtre « statut = valide » (faille de sécurité).
            $q->where(function (Builder $w) use ($t, $motif, $like, $pg, $mysql) {
                if ($mysql) {
                    // Recherche plein texte MySQL : rapide (utilise l'index FULLTEXT), comprend les mots
                    // entiers uniquement : « menuisier » est trouvé, mais pas « menui ».
                    // Les colonnes citées doivent être EXACTEMENT celles de l'index de la migration.
                    $w->whereRaw('MATCH(nom, prenoms, metier, bio) AGAINST (? IN NATURAL LANGUAGE MODE)', [$t]);
                } elseif ($pg) {
                    // Équivalent PostgreSQL (utilisé dans le projet final de l'équipe)
                    $w->whereRaw(
                        "to_tsvector('french', coalesce(nom,'')||' '||coalesce(prenoms,'')||' '||coalesce(metier,'')||' '||coalesce(bio,'')) @@ websearch_to_tsquery('french', ?)",
                        [$t]
                    );
                }

                // Dans tous les cas, on ajoute la recherche partielle : elle trouve les DEBUTS de mots
                // (« ingé » trouve « ingénieur »), ce que le plein texte ne sait pas faire.
                $w->orWhere('nom', $like, $motif)
                  ->orWhere('prenoms', $like, $motif)
                  ->orWhere('metier', $like, $motif);
            });
        }

        // ---------------------------------------------------------------------------
        // 5. Tri : nom (par défaut), plus récents, ou niveau d'étude le plus élevé
        // ---------------------------------------------------------------------------
        match ($r->input('tri')) {
            'recent' => $q->latest(),
            // Sous-requête : on lit le « rang » du niveau d'étude pour trier du plus élevé au plus faible
            'niveau' => $q->orderByRaw('(select rang from niveaux_etude where niveaux_etude.id = profils.niveau_etude_id) desc')
                          ->orderBy('nom'),
            default => $q->orderBy('nom')->orderBy('prenoms'),
        };

        return $q;
    }

    /**
     * Neutralise les caractères spéciaux de LIKE (% et _) saisis par l'utilisateur.
     * Sans cela, taper « % » dans la recherche renverrait tous les profils.
     */
    private static function echapper(string $texte): string
    {
        return addcslashes($texte, '%_\\');
    }
}
