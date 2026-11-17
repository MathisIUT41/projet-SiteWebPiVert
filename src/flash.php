<?php

declare(strict_types=1);

/** Message affiché une seule fois sur la page suivante (toast). */
function flash(string $type, string $message): void
{
    if (!in_array($type, ['succes', 'erreur', 'info'], true)) {
        throw new InvalidArgumentException("Type de message inconnu : $type");
    }
    $_SESSION['flashs'][] = ['type' => $type, 'message' => $message];
}

/** Lit les messages en attente et les efface. */
function flashs_lire(): array
{
    $flashs = $_SESSION['flashs'] ?? [];
    unset($_SESSION['flashs']);

    return $flashs;
}
