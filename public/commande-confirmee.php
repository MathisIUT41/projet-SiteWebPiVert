<?php

declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

$client = client_connecte();
$commande = commande_par_id((int) ($_GET['id'] ?? 0));

// Seul le client de la commande (ou l'administration) peut la voir.
if ($client === null
    || $commande === null
    || ((int) $commande['commande']['id_client'] !== (int) $client['id_client'] && !peut_voir_admin($client))
) {
    rediriger('index.php');
}

rendu('pages/commande-confirmee.twig', [
    'page' => 'commande',
    'c' => $commande,
]);
