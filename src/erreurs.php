<?php

declare(strict_types=1);

/**
 * Page affichée quand une erreur inattendue survient : aucun détail technique
 * (chemin, requête SQL, message d'exception) n'est montré au visiteur.
 */
function html_page_erreur(): string
{
    try {
        return rendu_chaine('pages/erreur.twig', ['page' => 'erreur']);
    } catch (Throwable) {
        return '<!doctype html><html lang="fr"><meta charset="utf-8"><title>Erreur · PiVert</title>'
            . '<body style="font-family:system-ui;padding:40px"><h1>Une erreur est survenue</h1>'
            . '<p>Réessaie dans un instant. <a href="index.php">Retour à l\'accueil</a></p></body></html>';
    }
}

/** Gestionnaire global : journalise l'erreur et affiche la page d'erreur (code 500). */
function gerer_exception(Throwable $e): void
{
    error_log('[PiVert] ' . $e::class . ' : ' . $e->getMessage() . ' dans ' . $e->getFile() . ':' . $e->getLine());

    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
    }

    echo html_page_erreur();
}
