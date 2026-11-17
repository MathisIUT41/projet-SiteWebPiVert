<?php

declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

$client = client_connecte();
if ($client === null) {
    flash('info', 'Connecte-toi pour valider ta commande.');
    rediriger('compte.php?retour=commande.php');
}

$panier = is_array($_SESSION['panier'] ?? null) ? $_SESSION['panier'] : [];

if (est_post()) {
    exiger_csrf();

    // On commande exactement ce qui était affiché sur le récapitulatif.
    $attendu = panier_depuis_formulaire(is_array($_POST['attendu'] ?? null) ? $_POST['attendu'] : []);
    $aCommander = commande_a_passer($attendu, $panier);

    if ($aCommander === null) {
        // Panier modifié (autre onglet) ou déjà commandé (double envoi).
        if ($panier !== []) {
            flash('info', 'Ton panier a changé : vérifie le récapitulatif avant de confirmer.');
        }
        rediriger($panier === [] ? 'panier.php' : 'commande.php');
    }

    try {
        $idCommande = commande_creer((int) $client['id_client'], $aCommander);
    } catch (StockInsuffisant $e) {
        flash('erreur', $e->getMessage() . ' Ton panier a été mis à jour.');
        rediriger('panier.php');
    }

    foreach (array_keys($aCommander) as $id) {
        unset($panier[$id]);
    }
    $_SESSION['panier'] = $panier;
    rediriger('commande-confirmee.php?id=' . $idCommande);
}

$detail = panier_detail($panier);
$lignes = array_values(array_filter($detail['lignes'], fn (array $l) => $l['quantite'] > 0));
$ruptures = array_values(array_filter($detail['lignes'], fn (array $l) => $l['quantite'] === 0));

if ($lignes === []) {
    rediriger('panier.php');
}

// Le récapitulatif fait foi : la session est ramenée aux quantités affichées.
foreach ($lignes as $ligne) {
    $panier[$ligne['id_produit']] = $ligne['quantite'];
}
$_SESSION['panier'] = array_intersect_key($panier, array_column($detail['lignes'], null, 'id_produit'));

rendu('pages/commande.twig', [
    'page' => 'commande',
    'lignes' => $lignes,
    'ruptures' => $ruptures,
    'total' => round(array_sum(array_column($lignes, 'sous_total')), 2),
]);
