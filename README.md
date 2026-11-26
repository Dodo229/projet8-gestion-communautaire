# Prototype du membre 4 : Recherche et consultation des profils (MySQL)

Ce kit contient **uniquement votre partie**, avec juste assez du reste du projet pour qu'elle fonctionne.
Il sert à voir concrètement ce que vous devez écrire, comprendre et défendre.

## 1. Ce qui est à vous, ce qui ne l'est pas

Chaque fichier commence par un commentaire qui indique son propriétaire.

| Fichier | Propriétaire |
|---|---|
| `backend/app/Services/FiltreProfils.php` | **VOUS** : le coeur de la recherche |
| `backend/app/Http/Controllers/Api/ProfilController.php` (index, show, photo) | **VOUS** |
| `backend/app/Models/Profil.php` (`toPublic`, `scopeValide`) | **VOUS** (le reste du modèle est partagé) |
| `backend/tests/Feature/AnnuaireTest.php` | **VOUS** : 10 tests |
| `frontend/src/pages/Annuaire.jsx` | **VOUS** |
| `frontend/src/pages/ProfilDetail.jsx` | **VOUS** |
| `frontend/src/components/CarteProfil.jsx` | **VOUS** |
| `Pagination` et `Avatar` dans `frontend/src/components/ui.jsx` | **VOUS** (avec précaution, d'autres les utilisent) |
| migration, seeders, `routes/api.php`, `api/client.js`, `App.jsx`, `Layout.jsx`, reste de `ui.jsx` | Membre 1 (copies réduites) |
| `AuthController`, `AuthContext.jsx`, `Connexion.jsx` | Membre 2 (copies pour tester le téléphone) |
| `ReferenceController` (listes de filtres) | Membre 5 (copie réduite) |

## 2. Installation (Windows, Laragon, MySQL)

**Pré-requis** : Laragon (Full), Node.js (inclus dans Laragon). MySQL est inclus dans Laragon : pas d'installation à part.

### 2.1 Démarrer MySQL et créer la base

1. Ouvrez Laragon et cliquez sur **Démarrer tout** (Apache et MySQL doivent passer au vert).
2. Dans le terminal Laragon :
   ```
   mysql -u root -e "CREATE DATABASE membre4 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
   ```
   (ou avec HeidiSQL, installé avec Laragon : bouton *Base de données*, nouvelle base `membre4`, encodage utf8mb4).

### 2.2 Back-end

```
cd C:\laragon\www
composer create-project laravel/laravel api-membre4
cd api-membre4
php artisan install:api
```

Répondez **no** si une question est posée. Copiez les dossiers `app`, `config`, `database`, `routes` et `tests` de ce kit (`backend\`) dans `api-membre4`, en remplaçant les fichiers.

Ouvrez le projet avec `code .` puis :

1. **`.env`** : remplacez le bloc `DB_...` par le contenu de `backend\.env.mysql.example`. Si une ligne `SESSION_DRIVER=database` existe, supprimez-la.
2. **`bootstrap/app.php`** : dans `->withMiddleware(function (Middleware $middleware) {`, ajoutez :
   ```php
   $middleware->statefulApi();
   ```
3. Dans le terminal :
   ```
   php artisan migrate --seed
   php artisan serve
   ```
   La migration doit afficher des lignes `DONE`. Le seeder crée 35 profils (32 validés, 3 en attente). Laissez ce terminal ouvert.
4. Test rapide dans le navigateur : `http://localhost:8000/api/v1/profils` doit afficher du JSON avec 12 profils.

### 2.3 Front-end

Dans un **second terminal** :

```
cd C:\laragon\www\<dossier du kit>\frontend
npm install
copy .env.example .env
npm run dev
```

Si PowerShell affiche « l'exécution de scripts est désactivée », tapez une fois :
`Set-ExecutionPolicy -Scope CurrentUser -ExecutionPolicy RemoteSigned`, puis rouvrez le terminal (ou utilisez le terminal de Laragon).

Ouvrez http://localhost:5173.

## 3. Parcours de test

1. La liste affiche 12 cartes et un compteur : « 32 profils trouvés ».
2. Pagination : passez à la page 2 puis 3 ; l'adresse change (`?page=2`).
3. Choisissez un secteur : la liste se réduit et la page revient à 1.
4. Combinez secteur + localisation (`nord`) + niveau d'étude.
5. Tapez `ingenieur` (sans accent) : « Ingénieur » est trouvé. Tapez `ingé` : trouvé aussi.
6. Tapez `zzzz` : message « Aucun profil ne correspond ».
7. Copiez l'adresse, ouvrez-la dans un nouvel onglet : la même recherche s'affiche.
8. Ouvrez un profil : compétences, expériences (Houngbo Adjovi en a deux).
9. Cliquez sur une compétence : l'annuaire se filtre dessus (pastille « Compétence : ... »).
10. Téléphone : masqué, sauf profils publics. Connectez-vous avec `houngbo0@exemple.local` / `Demo-2026-pass` : les téléphones apparaissent.
11. Arrêtez l'API (`Ctrl + C` dans le terminal 1) et rechargez : un message d'erreur s'affiche, pas une page blanche.
12. Ouvrez F12, onglet *Réseau* : observez l'appel `profils?...` avec ses paramètres.

## 4. Le trajet complet d'une recherche

```
Navigateur (React)                      Serveur (Laravel)                     MySQL
---------------------------------------------------------------------------------------------
Annuaire.jsx
  l'utilisateur choisit un secteur
  changer('secteur_id', 4)
  -> l'adresse devient ?secteur_id=4
  -> useEffect se déclenche
  -> api.get('/profils', {params})  --->  routes/api.php
                                          ProfilController@index
                                            Profil::valide()          (WHERE statut = 'valide')
                                            FiltreProfils::appliquer  (AND secteur_id = 4 ...)
                                            ->paginate(12)  ----------->  SELECT ... LIMIT 12
                                                                    <-----  lignes + total
                                            toPublic() sur chaque profil (masque le téléphone)
  setResultat(...)                  <---  JSON { data[], current_page, last_page, total }
  React réaffiche CarteProfil x 12
```

## 5. Ce que MySQL change (testé)

J'ai vérifié ces comportements sur un serveur MariaDB 10.11 (compatible MySQL) avec une base en `utf8mb4_unicode_ci` :

- **Majuscules et accents ignorés** : `LIKE '%ingenieur%'` trouve « Ingénieur ». PostgreSQL, lui, aurait besoin d'`ILIKE` et ne tolère pas l'accent manquant.
- **Index FULLTEXT** : `MATCH(...) AGAINST('menuisier')` trouve « Menuisier-ébéniste », mais **seulement les mots entiers** : `ingen` ne trouve rien. C'est pourquoi `FiltreProfils` ajoute `LIKE` à côté de `MATCH` pour les débuts de mots.
- **Les colonnes de `MATCH` doivent être exactement celles de l'index** de la migration (`nom, prenoms, metier, bio`, dans cet ordre). Sinon MySQL renvoie une erreur.
- **`FiltreProfils` détecte la base** : MySQL utilise `MATCH ... AGAINST`, PostgreSQL utilise `to_tsvector`, SQLite (tests) n'utilise que `LIKE`. Quand vous fusionnerez avec le projet de l'équipe (PostgreSQL), le même fichier fonctionnera.
- Les **tests** tournent sur SQLite en mémoire : votre base MySQL n'est pas touchée.

## 6. Voir le SQL généré

```
php artisan tinker
```
```php
$r = Illuminate\Http\Request::create('/x', 'GET', ['secteur_id' => 4, 'q' => 'dév', 'tri' => 'niveau']);
App\Services\FiltreProfils::appliquer(App\Models\Profil::valide(), $r)->toSql();
```
Le résultat montre la requête SQL avec ses `?` (les valeurs sont envoyées à part, ce qui empêche les injections SQL). Pour voir les requêtes réellement exécutées par une page, installez Laravel Debugbar ou lisez `DB::enableQueryLog()`.

## 7. Tests automatiques

```
php artisan test
```
10 tests : profils non validés exclus, filtre par secteur, filtres combinés, début de mot, recherche qui ne contourne pas le statut, compétence, pagination, détail introuvable, téléphone masqué puis visible, aucune donnée sensible exposée.

## 8. Vos tickets d'amélioration

Une fois le prototype compris, voici du travail réel à ajouter, du plus simple au plus difficile :

| N° | Ticket | Difficulté | Fichiers |
|---|---|---|---|
| 1 | Afficher une pastille retirable pour chaque filtre actif (pas seulement la compétence) | Facile | `Annuaire.jsx` |
| 2 | Choix du nombre de profils par page (12, 24, 48) via un paramètre `par_page` borné côté serveur | Facile | `ProfilController`, `Annuaire.jsx` |
| 3 | Squelettes de chargement à la place du texte « Chargement... » | Facile | `Annuaire.jsx`, `ui.jsx` |
| 4 | Surligner le mot recherché dans les cartes | Moyen | `CarteProfil.jsx` |
| 5 | Filtre par plusieurs compétences à la fois | Moyen | `FiltreProfils`, `Annuaire.jsx` |
| 6 | Bloc « Profils similaires » (même secteur) sur la page de détail, avec une nouvelle route | Moyen | `ProfilController`, `routes/api.php`, `ProfilDetail.jsx` |
| 7 | Tri par pertinence quand une recherche libre est active (score `MATCH ... AGAINST`) | Difficile | `FiltreProfils` |
| 8 | Mesure de performance : seeder de 10 000 profils, `EXPLAIN` d'une requête avec et sans index, capture pour la soutenance | Difficile | seeder, pgAdmin ou HeidiSQL |

## 9. Fusion avec le projet de l'équipe

- Ne copiez dans le dépôt commun **que vos fichiers** (tableau de la partie 1).
- Dans `ProfilController`, le projet complet contient aussi la méthode `store` (inscription) des membres 2 et 3 : ne la supprimez pas. Reprenez seulement vos trois méthodes `index`, `show` et `photo`.
- Ne remplacez pas `ui.jsx`, `Layout.jsx`, `App.jsx`, `routes/api.php` ni `client.js` par les copies réduites de ce kit.
- Dans le dépôt commun, les pages sont aux mêmes adresses (`/annuaire`, `/profils/:id`) : la fusion ne demande aucun renommage.
