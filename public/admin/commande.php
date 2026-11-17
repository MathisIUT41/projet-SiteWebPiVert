<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/src/bootstrap.php';

exiger_admin();

// Récapitulatif d'une commande enregistrée.
if (isset($_GET['id'])) {
    $commande = commande_par_id((int) $_GET['id']);
    if ($commande === null) {
        flash('erreur', 'Cette commande n\'existe pas.');
        rediriger('commande.php');
    }
    rendu_admin('admin/commande-recap.twig', ['page_admin' => 'commande', 'c' => $commande]);
    exit;
}

$clients = clients_pour_commande();
$erreur = null;
$saisie = ['id_client' => 0, 'quantites' => []];

if (est_post()) {
    exiger_ecriture_admin();
    exiger_csrf();

    $idClient = entier_post('id_client');
    $panier = panier_depuis_formulaire((array) ($_POST['quantites'] ?? []));
    $saisie = ['id_client' => $idClient, 'quantites' => $panier];

    if (!in_array($idClient, array_map('intval', array_column($clients, 'id_client')), true)) {
        $erreur = 'Choisis le client pour qui passer la commande.';
    } elseif ($panier === []) {
        $erreur = 'Indique au moins une quantité.';
    } else {
        try {
            $idCommande = commande_creer($idClient, $panier);
            flash('succes', 'Commande enregistrée');
            rediriger('commande.php?id=' . $idCommande);
        } catch (StockInsuffisant $e) {
            $erreur = $e->getMessage();
        }
    }
}

// Produits en stock, regroupés par catégorie.
$groupes = [];
foreach (produits_admin() as $p) {
    if ($p['quantite'] > 0) {
        $groupes[$p['slug_categorie']]['libelle'] = $p['libelle_categorie'];
        $groupes[$p['slug_categorie']]['univers'] = $p['univers'];
        $groupes[$p['slug_categorie']]['produits'][] = $p;
    }
}

rendu_admin('admin/commande-form.twig', [
    'page_admin' => 'commande',
    'clients' => $clients,
    'groupes' => $groupes,
    'saisie' => $saisie,
    'erreur' => $erreur,
]);
