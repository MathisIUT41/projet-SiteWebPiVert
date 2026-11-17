<?php

declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

if (!est_post()) {
    rediriger('panier.php');
}

exiger_csrf();

$action = en_texte($_POST['action'] ?? '');
$id = entier_post('id');
$qte = entier_post('qte', 0);
$retour = url_retour_sure($_POST['retour'] ?? null, 'panier.php');
$panier = is_array($_SESSION['panier'] ?? null) ? $_SESSION['panier'] : [];

if ($action === 'retirer') {
    $_SESSION['panier'] = panier_retirer($panier, $id);
    flash('succes', 'Produit retiré');
    rediriger($retour);
}

$stock = stock_produit($id);
if ($stock === null) {
    $_SESSION['panier'] = panier_retirer($panier, $id);
    flash('erreur', 'Ce produit n\'existe plus.');
    rediriger($retour);
}

if ($action === 'ajouter') {
    if ($stock <= 0) {
        flash('erreur', 'Ce produit est en rupture de stock.');
    } elseif ($qte <= 0) {
        flash('erreur', 'Choisis une quantité d\'au moins 1.');
    } else {
        $demande = ($panier[$id] ?? 0) + $qte;
        $_SESSION['panier'] = panier_ajouter($panier, $id, $qte, $stock);
        $demande > $stock
            ? flash('info', "Il n'en reste que $stock : la quantité a été ajustée.")
            : flash('succes', 'Ajouté au panier');
    }
} elseif ($action === 'modifier') {
    $_SESSION['panier'] = panier_modifier($panier, $id, $qte, $stock);
    if ($qte <= 0) {
        flash('succes', 'Produit retiré');
    } elseif ($qte > $stock) {
        flash('info', "Il n'en reste que $stock : la quantité a été ajustée.");
    } else {
        flash('succes', 'Panier mis à jour');
    }
} else {
    flash('erreur', 'Action inconnue.');
}

rediriger($retour);
