<?php

declare(strict_types=1);

/**
 * (Re)crée une base complète à partir de sql/schema.sql puis sql/donnees.sql.
 * Le nom de base vient de la configuration, jamais d'une saisie utilisateur.
 */
function installer_base(mysqli $cnx, string $nomBase, string $dossierSql): void
{
    if (!preg_match('/^[a-z0-9_]+$/', $nomBase)) {
        throw new InvalidArgumentException("Nom de base invalide : $nomBase");
    }

    $cnx->query("DROP DATABASE IF EXISTS `$nomBase`");
    $cnx->query("CREATE DATABASE `$nomBase` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $cnx->select_db($nomBase);

    foreach (['schema.sql', 'donnees.sql'] as $fichier) {
        $sql = file_get_contents($dossierSql . '/' . $fichier);
        if ($sql === false) {
            throw new RuntimeException("Fichier SQL introuvable : $fichier");
        }
        if (trim(preg_replace('/^\s*--.*$/m', '', $sql)) === '') {
            continue;
        }
        $cnx->multi_query($sql);
        // Vider tous les résultats pour que la connexion reste utilisable.
        do {
            $resultat = $cnx->store_result();
            if ($resultat instanceof mysqli_result) {
                $resultat->free();
            }
        } while ($cnx->more_results() && $cnx->next_result());
    }
}
