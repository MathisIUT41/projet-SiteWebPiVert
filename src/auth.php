<?php

declare(strict_types=1);

/**
 * Vérifie le formulaire d'inscription.
 * @return array{donnees: array, erreurs: array<string, string>}
 */
function valider_inscription(array $post): array
{
    $d = [];
    foreach (['nom', 'prenom', 'email', 'mot_de_passe', 'adresse'] as $champ) {
        $d[$champ] = trim((string) ($post[$champ] ?? ''));
    }

    $erreurs = [];
    if ($d['nom'] === '') {
        $erreurs['nom'] = 'Indique ton nom.';
    }
    if ($d['prenom'] === '') {
        $erreurs['prenom'] = 'Indique ton prénom.';
    }
    if (filter_var($d['email'], FILTER_VALIDATE_EMAIL) === false) {
        $erreurs['email'] = 'Cette adresse e-mail n\'est pas valide.';
    }
    if (mb_strlen($d['mot_de_passe']) < 8) {
        $erreurs['mot_de_passe'] = 'Le mot de passe doit faire au moins 8 caractères.';
    }
    if ($d['adresse'] === '') {
        $erreurs['adresse'] = 'Indique ton adresse de livraison.';
    }

    return ['donnees' => $d, 'erreurs' => $erreurs];
}

/** Crée un compte client. Renvoie son id, ou 'email_pris'. */
function inscrire(array $donnees): int|string
{
    if (db_un('SELECT id_client FROM CLIENT WHERE email = ?', 's', [$donnees['email']]) !== null) {
        return 'email_pris';
    }

    db_executer(
        "INSERT INTO CLIENT (nom, prenom, email, mot_de_passe, adresse, role, date_inscription)
         VALUES (?, ?, ?, ?, ?, 'client', CURDATE())",
        'sssss',
        [
            $donnees['nom'],
            $donnees['prenom'],
            $donnees['email'],
            password_hash($donnees['mot_de_passe'], PASSWORD_DEFAULT),
            $donnees['adresse'],
        ]
    );

    return db()->insert_id;
}

/** Connecte le client si l'e-mail et le mot de passe correspondent. */
function connecter(string $email, string $motDePasse): ?array
{
    $ligne = db_un('SELECT * FROM CLIENT WHERE email = ?', 's', [trim($email)]);

    if ($ligne === null || !password_verify($motDePasse, $ligne['mot_de_passe'])) {
        return null;
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }

    $client = [
        'id_client' => (int) $ligne['id_client'],
        'prenom' => $ligne['prenom'],
        'nom' => $ligne['nom'],
        'email' => $ligne['email'],
        'adresse' => $ligne['adresse'],
        'role' => $ligne['role'],
    ];
    $_SESSION['client'] = $client;

    return $client;
}

/** Déconnecte le client mais garde son panier. */
function deconnecter(): void
{
    unset($_SESSION['client']);

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

function client_connecte(): ?array
{
    return $_SESSION['client'] ?? null;
}

function peut_voir_admin(?array $client): bool
{
    return in_array($client['role'] ?? null, ['admin', 'demo'], true);
}

function peut_modifier_admin(?array $client): bool
{
    return ($client['role'] ?? null) === 'admin';
}
