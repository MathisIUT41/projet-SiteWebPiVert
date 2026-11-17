<?php

declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

$panier = is_array($_SESSION['panier'] ?? null) ? $_SESSION['panier'] : [];
$detail = panier_detail($panier);

// Remet la session d'accord avec la base : produits disparus retirés, quantités ramenées au stock.
// Les produits passés en rupture restent affichés (« Plus en stock ») jusqu'à ce que le client les retire.
$synchro = [];
foreach ($detail['lignes'] as $ligne) {
    $synchro[$ligne['id_produit']] = $ligne['stock'] > 0 ? $ligne['quantite'] : (int) $panier[$ligne['id_produit']];
}
$_SESSION['panier'] = $synchro;

rendu('pages/panier.twig', [
    'page' => 'panier',
    'lignes' => $detail['lignes'],
    'total' => $detail['total'],
    'commande_possible' => $detail['total'] > 0,
]);
