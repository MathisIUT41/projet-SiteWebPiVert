<?php

declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

$univers = en_texte($_GET['u'] ?? '');
if (!isset(UNIVERS[$univers])) {
    rediriger('index.php');
}

$filtre = filtre_valide($univers, isset($_GET['filtre']) ? en_texte($_GET['filtre']) : null);

rendu('pages/univers.twig', [
    'univers' => $univers,
    'titre_univers' => UNIVERS[$univers],
    'filtres' => filtres_univers($univers),
    'filtre_actif' => $filtre,
    'produits' => ajouter_caracteristiques(produits_univers($univers, $filtre)),
    'conseils' => conseils($univers),
]);
