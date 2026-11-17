<?php

declare(strict_types=1);

/** Redirige puis arrête le script. */
function rediriger(string $url): never
{
    header('Location: ' . $url, true, 302);
    exit;
}

function est_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/** Entier envoyé en POST, ou la valeur par défaut si absent / invalide. */
function entier_post(string $cle, int $defaut = 0): int
{
    $valeur = filter_var($_POST[$cle] ?? null, FILTER_VALIDATE_INT);

    return $valeur === false || $valeur === null ? $defaut : $valeur;
}

/**
 * Adresse de retour après une action : uniquement un chemin relatif au site
 * (pas de schéma, pas de « // », pas d'antislash) pour éviter les redirections ouvertes.
 */
function url_retour_sure(?string $url, string $defaut = 'index.php'): string
{
    $url = trim((string) $url);

    if ($url === ''
        || str_contains($url, '\\')
        || str_starts_with($url, '/')
        || preg_match('#^[a-z][a-z0-9+.-]*:#i', $url)
        || preg_match('/[\x00-\x1F]/', $url)
    ) {
        return $defaut;
    }

    return $url;
}

/** Page courante (chemin relatif + requête), utilisée comme adresse de retour des formulaires. */
function page_courante(): string
{
    $chemin = basename(parse_url($_SERVER['REQUEST_URI'] ?? 'index.php', PHP_URL_PATH) ?: 'index.php');
    $requete = $_SERVER['QUERY_STRING'] ?? '';

    return $requete === '' ? $chemin : $chemin . '?' . $requete;
}

/** Page précédente (Referer) réduite à un chemin relatif sûr, sinon la valeur par défaut. */
function referer_relatif(string $defaut = 'index.php'): string
{
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $chemin = basename((string) parse_url($referer, PHP_URL_PATH));
    $requete = (string) parse_url($referer, PHP_URL_QUERY);

    return url_retour_sure($chemin === '' ? '' : $chemin . ($requete === '' ? '' : '?' . $requete), $defaut);
}
