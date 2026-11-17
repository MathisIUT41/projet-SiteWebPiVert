<?php

declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

rendu('pages/conseils.twig', [
    'page' => 'conseils',
    'conseils' => ['vegetal' => conseils('vegetal'), 'animalier' => conseils('animalier')],
]);
