<?php

declare(strict_types=1);

/**
 * Configuration de l'application (fichier config.php à la racine, non versionné).
 * En environnement de test (PIVERT_ENV=test), la base de test remplace la base normale.
 */
function config(): array
{
    static $config = null;

    if ($config === null) {
        $fichier = dirname(__DIR__) . '/config.php';
        if (!is_file($fichier)) {
            throw new RuntimeException('config.php introuvable : copie config.exemple.php en config.php.');
        }
        $config = require $fichier;
    }

    if (getenv('PIVERT_ENV') === 'test') {
        return ['db' => $config['db_test']] + $config;
    }

    return $config;
}
