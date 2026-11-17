<?php

declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

$retour = url_retour_sure($_POST['retour'] ?? $_GET['retour'] ?? null);
if (str_starts_with($retour, 'compte.php') || str_starts_with($retour, 'deconnexion.php')) {
    $retour = 'index.php';
}

if (!est_post() && client_connecte() !== null) {
    rediriger($retour);
}

$onglet = ($_GET['onglet'] ?? '') === 'inscription' ? 'inscription' : 'connexion';
$erreurConnexion = null;
$erreurs = [];
$saisie = [];

if (est_post()) {
    exiger_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'connexion') {
        $client = connecter((string) ($_POST['email'] ?? ''), (string) ($_POST['mot_de_passe'] ?? ''));
        if ($client !== null) {
            flash('succes', "Bienvenue, {$client['prenom']} !");
            rediriger($retour);
        }
        $erreurConnexion = 'E-mail ou mot de passe incorrect.';
        $saisie = ['email' => trim((string) ($_POST['email'] ?? ''))];
    }

    if ($action === 'inscription') {
        $onglet = 'inscription';
        ['donnees' => $donnees, 'erreurs' => $erreurs] = valider_inscription($_POST);

        if ($erreurs === []) {
            $resultat = inscrire($donnees);
            if ($resultat === 'email_pris') {
                $erreurs['email'] = 'Un compte existe déjà avec cette adresse. Connecte-toi plutôt.';
            } else {
                connecter($donnees['email'], $donnees['mot_de_passe']);
                flash('succes', "Bienvenue, {$donnees['prenom']} ! Ton compte est créé.");
                rediriger($retour);
            }
        }

        unset($donnees['mot_de_passe']);
        $saisie = $donnees;
    }
}

rendu('pages/compte.twig', [
    'onglet' => $onglet,
    'retour' => $retour,
    'erreur_connexion' => $erreurConnexion,
    'erreurs' => $erreurs,
    'saisie' => $saisie,
]);
