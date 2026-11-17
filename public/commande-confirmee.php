<?php

declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

$client = client_connecte();
$commande = commande_par_id((int) ($_GET['id'] ?? 0));

// Seul le client de la commande (ou l'administrateur) peut la voir.
if ($commande === null || !peut_voir_commande($commande, $client)) {
    rediriger('index.php');
}

rendu('pages/commande-confirmee.twig', [
    'page' => 'commande',
    'c' => $commande,
]);
