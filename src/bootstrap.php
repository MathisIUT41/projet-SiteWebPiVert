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
