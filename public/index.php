<?php

declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

rendu('pages/accueil.twig', [
    'page' => 'accueil',
    'mis_en_avant' => ajouter_caracteristiques(produits_mis_en_avant(4)),
    'conseil_du_jour' => conseil_du_jour(new DateTimeImmutable('now', new DateTimeZone('Europe/Paris'))),
    'nb_rayons' => [
        'vegetal' => count(filtres_univers('vegetal')),
        'animalier' => count(filtres_univers('animalier')),
    ],
]);
