<?php

declare(strict_types=1);

/**
 * Vérifie le formulaire d'inscription.
 * @return array{donnees: array, erreurs: array<string, string>}
 */
function valider_inscription(array $post): array
{
    $d = [];
    foreach (['nom', 'prenom', 'email', 'adresse'] as $champ) {
        $d[$champ] = trim(en_texte($post[$champ] ?? ''));
    }
    // Le mot de passe est gardé tel quel : la connexion ne le modifie pas non plus.
    $d['mot_de_passe'] = en_texte($post['mot_de_passe'] ?? '');

    $erreurs = [];
    if ($d['nom'] === '' || mb_strlen($d['nom']) > 255) {
        $erreurs['nom'] = 'Indique ton nom (255 caractères maximum).';
    }
    if ($d['prenom'] === '' || mb_strlen($d['prenom']) > 255) {
        $erreurs['prenom'] = 'Indique ton prénom (255 caractères maximum).';
    }
    if (mb_strlen($d['email']) > 255 || filter_var($d['email'], FILTER_VALIDATE_EMAIL) === false) {
        $erreurs['email'] = 'Cette adresse e-mail n\'est pas valide.';
    }
    if (mb_strlen($d['mot_de_passe']) < 8 || strlen($d['mot_de_passe']) > 72) {
        $erreurs['mot_de_passe'] = 'Le mot de passe doit faire entre 8 et 72 caractères.';
    }
    if ($d['adresse'] === '' || mb_strlen($d['adresse']) > 255) {
        $erreurs['adresse'] = 'Indique ton adresse de livraison (255 caractères maximum).';
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

/** Seul le rôle admin peut modifier (le rôle demo voit l'admin en lecture seule). */
function ecriture_admin_autorisee(?array $client): bool
{
    return peut_modifier_admin($client);
}

/**
 * Où envoyer quelqu'un qui n'a pas accès à /admin : la connexion s'il est anonyme,
 * l'accueil s'il est déjà connecté (sinon compte.php le renverrait ici, en boucle).
 */
function destination_refus_admin(?array $client): string
{
    return $client === null ? '../compte.php?retour=admin/index.php' : '../index.php';
}

/** Garde d'accès à /admin : renvoie le client, sinon redirige. */
function exiger_admin(): array
{
    $client = client_connecte();
    if (!peut_voir_admin($client)) {
        flash('erreur', 'Accès réservé à l\'administration.');
        rediriger(destination_refus_admin($client));
    }

    return $client;
}

/** Garde d'écriture : à appeler en premier dans toute branche POST de /admin. */
function exiger_ecriture_admin(): void
{
    if (!ecriture_admin_autorisee(client_connecte())) {
        flash('erreur', 'Mode démo : les modifications sont désactivées');
        rediriger(referer_relatif('index.php'));
    }
}

/**
 * Comptes clients pour lesquels l'admin peut passer une commande.
 * Le compte démo (public) ne voit que les comptes de démonstration.
 */
function clients_pour_commande(?array $lecteur = null): array
{
    $sql = "SELECT id_client, prenom, nom, email FROM CLIENT WHERE role = 'client'";
    if (($lecteur['role'] ?? null) === 'demo') {
        $sql .= " AND email LIKE '%@pivert.demo'";
    }

    return db_tous($sql . ' ORDER BY nom, prenom');
}

/** Vrai pour les comptes de démonstration (données fictives, publiables). */
function est_compte_de_demo(array $client): bool
{
    return str_ends_with((string) ($client['email'] ?? ''), '@pivert.demo');
}

/**
 * Données d'un client telles qu'on peut les montrer à ce lecteur :
 * le compte démo ne voit jamais les coordonnées des vrais clients.
 */
function client_affichable(array $client, ?array $lecteur): array
{
    if (($lecteur['role'] ?? null) !== 'demo' || est_compte_de_demo($client)) {
        return $client;
    }

    return ['nom' => '', 'email' => 'Masqué en mode démo', 'adresse' => 'Masquée en mode démo'] + $client;
}

/** Seuls le client de la commande et l'administrateur peuvent la voir. */
function peut_voir_commande(array $commande, ?array $lecteur): bool
{
    if ($lecteur === null) {
        return false;
    }

    return ($lecteur['role'] ?? null) === 'admin'
        || (int) ($commande['commande']['id_client'] ?? 0) === (int) ($lecteur['id_client'] ?? -1);
}
