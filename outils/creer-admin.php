<?php

declare(strict_types=1);

// Usage : php outils/creer-admin.php email motdepasse prenom nom [--test]
// Crée le compte administrateur (ou met à jour son mot de passe et son rôle).
// Le mot de passe n'est jamais écrit dans les fichiers du dépôt.

if (PHP_SAPI !== 'cli') {
    exit("À lancer en ligne de commande.\n");
}

$args = array_values(array_filter(array_slice($argv, 1), fn ($a) => $a !== '--test'));
if (in_array('--test', $argv, true)) {
    putenv('PIVERT_ENV=test');
}

if (count($args) !== 4) {
    fwrite(STDERR, "Usage : php outils/creer-admin.php email motdepasse prenom nom\n");
    exit(1);
}

[$email, $motDePasse, $prenom, $nom] = $args;

if (filter_var($email, FILTER_VALIDATE_EMAIL) === false || mb_strlen($motDePasse) < 8) {
    fwrite(STDERR, "E-mail invalide ou mot de passe trop court (8 caractères minimum).\n");
    exit(1);
}

require dirname(__DIR__) . '/src/bootstrap.php';

db_executer(
    "INSERT INTO CLIENT (nom, prenom, email, mot_de_passe, adresse, role, date_inscription)
     VALUES (?, ?, ?, ?, '—', 'admin', CURDATE())
     ON DUPLICATE KEY UPDATE mot_de_passe = VALUES(mot_de_passe), role = 'admin'",
    'ssss',
    [$nom, $prenom, $email, password_hash($motDePasse, PASSWORD_DEFAULT)]
);

echo "Compte administrateur prêt : $email\n";
