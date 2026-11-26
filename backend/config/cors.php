<?php

/*
|--------------------------------------------------------------------------
| CORS (Cross-Origin Resource Sharing)
|--------------------------------------------------------------------------
| En développement, le site React (localhost:5173) et l'API Laravel
| (localhost:8000) n'ont pas la même origine. Le navigateur bloque alors les
| requêtes, sauf si l'API les autorise explicitement : c'est le rôle de ce
| fichier. En production avec un seul domaine, CORS n'est plus sollicité.
*/

return [
    // Chemins de l'API concernés par ces règles
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Seule l'adresse du site React est autorisée à appeler l'API
    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:5173')],

    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => ['Content-Disposition'],   // nécessaire pour lire le nom des fichiers téléchargés
    'max_age' => 0,

    // Indispensable : autorise l'envoi des cookies de session (authentification Sanctum)
    'supports_credentials' => true,
];
