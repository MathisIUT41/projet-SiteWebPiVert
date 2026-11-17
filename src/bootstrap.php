<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => true,
    ]);
}

foreach (glob(__DIR__ . '/*.php') as $fichier) {
    if (basename($fichier) !== 'bootstrap.php') {
        require_once $fichier;
    }
}

if (PHP_SAPI !== 'cli') {
    // En ligne : jamais de détail technique à l'écran, une page d'erreur propre à la place.
    ini_set('display_errors', !empty(config()['debug']) ? '1' : '0');
    set_exception_handler('gerer_exception');
}
