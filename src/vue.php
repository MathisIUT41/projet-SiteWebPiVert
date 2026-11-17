<?php

declare(strict_types=1);

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;

/** Moteur Twig partagé (autoescape HTML activé). */
function twig(): Environment
{
    static $twig = null;

    if ($twig === null) {
        $twig = new Environment(new FilesystemLoader(dirname(__DIR__) . '/templates'), [
            'autoescape' => 'html',
            'cache' => false,
            'strict_variables' => false,
        ]);

        $twig->addFilter(new TwigFilter('prix', 'prix_formate'));
        $twig->addFunction(new TwigFunction('image_produit', 'image_produit'));
    }

    return $twig;
}

/** 24.9 → « 24,90 € » (espace fine insécable avant l'euro). */
function prix_formate(float|int|string $montant): string
{
    return number_format((float) $montant, 2, ',', "\u{202F}") . "\u{202F}€";
}

/** Chemin de la photo d'un produit, ou de l'image par défaut. */
function image_produit(?string $photo): string
{
    return 'images/produits/' . (($photo ?? '') === '' ? 'defaut.svg' : $photo);
}

/** Rend un template avec les variables communes à toutes les pages. */
function rendu_chaine(string $template, array $vars = []): string
{
    $panier = $_SESSION['panier'] ?? [];

    $communes = [
        'client' => $_SESSION['client'] ?? null,
        'panier_nombre' => is_array($panier) ? panier_nombre($panier) : 0,
        'flashs' => flashs_lire(),
        'csrf' => csrf_jeton(),
        'univers' => $vars['univers'] ?? null,
        'base_url' => $vars['base_url'] ?? '',
        'page_courante' => PHP_SAPI === 'cli' ? 'index.php' : page_courante(),
    ];

    return twig()->render($template, $vars + $communes);
}

function rendu(string $template, array $vars = []): void
{
    echo rendu_chaine($template, $vars);
}

/** Rendu d'une page de /admin (chemins relatifs au dossier parent). */
function rendu_admin(string $template, array $vars = []): void
{
    rendu($template, $vars + ['base_url' => '../']);
}
