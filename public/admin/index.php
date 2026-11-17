<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/src/bootstrap.php';

exiger_admin();

rendu_admin('admin/tableau-de-bord.twig', [
    'page_admin' => 'tableau',
    'stats' => stats_stock(),
    'produits' => produits_admin(),
    'categories' => categories(),
]);
