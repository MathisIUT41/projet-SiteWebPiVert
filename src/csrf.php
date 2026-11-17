<?php

declare(strict_types=1);

/** Jeton anti-CSRF de la session (créé à la première demande). */
function csrf_jeton(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}

function csrf_valide(mixed $jeton): bool
{
    return is_string($jeton) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $jeton);
}

/** Arrête l'action si le formulaire n'a pas de jeton valide. */
function exiger_csrf(): void
{
    if (csrf_valide($_POST['csrf'] ?? null)) {
        return;
    }

    flash('erreur', 'Session expirée, réessaie.');
    rediriger(referer_relatif());
}
