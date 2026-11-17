<?php

declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

$client = client_connecte();
if ($client === null) {
    flash('info', 'Connecte-toi pour valider ta commande.');
    rediriger('compte.php?retour=commande.php');
}

$detail = panier_detail(is_array($_SESSION['panier'] ?? null) ? $_SESSION['panier'] : []);
$lignes = array_values(array_filter($detail['lignes'], fn (array $l) => $l['quantite'] > 0));
$ruptures = array_values(array_filter($detail['lignes'], fn (array $l) => $l['quantite'] === 0));

// Panier vide (ou déjà commandé, en cas de double envoi) : retour au panier.
if ($lignes === []) {
    rediriger('panier.php');
}

if (est_post()) {
    exiger_csrf();

    try {
        $idCommande = commande_creer((int) $client['id_client'], array_column($lignes, 'quantite', 'id_produit'));
    } catch (StockInsuffisant $e) {
        flash('erreur', $e->getMessage());
        rediriger('panier.php');
    }

    $_SESSION['panier'] = [];
    rediriger('commande-confirmee.php?id=' . $idCommande);
}

rendu('pages/commande.twig', [
    'page' => 'commande',
    'lignes' => $lignes,
    'ruptures' => $ruptures,
    'total' => $detail['total'],
]);
