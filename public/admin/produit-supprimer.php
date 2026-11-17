<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/src/bootstrap.php';

exiger_admin();

if (!est_post()) {
    rediriger('index.php');
}

exiger_ecriture_admin();
exiger_csrf();

$id = entier_post('id');
$produit = db_un('SELECT nom FROM PRODUIT WHERE id_produit = ?', 'i', [$id]);

if ($produit === null) {
    flash('erreur', 'Ce produit n\'existe pas.');
} elseif (produit_supprimer($id)) {
    flash('succes', "« {$produit['nom']} » supprimé");
} else {
    flash('erreur', 'Ce produit apparaît dans des commandes : passe son stock à 0 plutôt que de le supprimer.');
}

rediriger('index.php');
