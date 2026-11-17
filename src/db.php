<?php

declare(strict_types=1);

/**
 * Connexion unique à la base (mysqli, utf8mb4, erreurs en exceptions).
 */
function db(): mysqli
{
    static $cnx = null;

    if ($cnx === null) {
        $c = config()['db'];
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $cnx = new mysqli($c['host'], $c['utilisateur'], $c['mot_de_passe']);
        $cnx->set_charset('utf8mb4');
        $cnx->select_db($c['base']);
    }

    return $cnx;
}

/**
 * Prépare et exécute une requête ; les paramètres sont toujours liés (jamais concaténés).
 */
function db_requete(string $sql, string $types = '', array $params = []): mysqli_stmt
{
    $stmt = db()->prepare($sql);
    if ($params !== []) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();

    return $stmt;
}

/** Toutes les lignes (tableaux associatifs). */
function db_tous(string $sql, string $types = '', array $params = []): array
{
    $resultat = db_requete($sql, $types, $params)->get_result();

    return $resultat === false ? [] : $resultat->fetch_all(MYSQLI_ASSOC);
}

/** La première ligne, ou null. */
function db_un(string $sql, string $types = '', array $params = []): ?array
{
    return db_tous($sql, $types, $params)[0] ?? null;
}

/** Exécute une écriture et renvoie le nombre de lignes affectées. */
function db_executer(string $sql, string $types = '', array $params = []): int
{
    return db_requete($sql, $types, $params)->affected_rows;
}
