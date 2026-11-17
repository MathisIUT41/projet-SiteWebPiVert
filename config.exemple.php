<?php
// Copier ce fichier en config.php (non versionné) et adapter les identifiants.
return [
    // true en local pour voir les erreurs PHP ; false en ligne.
    'debug' => false,
    'db' => [
        'host'         => 'localhost',
        'utilisateur'  => 'root',
        'mot_de_passe' => '',
        'base'         => 'pivert',
    ],
    'db_test' => [
        'host'         => 'localhost',
        'utilisateur'  => 'root',
        'mot_de_passe' => '',
        'base'         => 'pivert_test',
    ],
];
