<?php

declare(strict_types=1);

// Usage : php outils/installer-base.php [--test]
// Supprime puis recrée la base (pivert, ou pivert_test avec --test) depuis sql/.

if (PHP_SAPI !== 'cli') {
    exit("À lancer en ligne de commande.\n");
}

if (in_array('--test', $argv, true)) {
    putenv('PIVERT_ENV=test');
}

require dirname(__DIR__) . '/src/bootstrap.php';

$c = config()['db'];
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$cnx = new mysqli($c['host'], $c['utilisateur'], $c['mot_de_passe']);
$cnx->set_charset('utf8mb4');

installer_base($cnx, $c['base'], dirname(__DIR__) . '/sql');

echo "Base « {$c['base']} » installée.\n";
