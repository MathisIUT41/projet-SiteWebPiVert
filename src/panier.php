<?php

declare(strict_types=1);

// Le panier vit en session : [id_produit => quantité].
// Les fonctions ci-dessous sont pures (elles reçoivent et renvoient le panier).

/** Ajoute $qte au produit, sans dépasser le stock. */
function panier_ajouter(array $panier, int $id, int $qte, int $stock): array
{
    if ($qte <= 0 || $stock <= 0) {
        return $panier;
    }

    $panier[$id] = min(($panier[$id] ?? 0) + $qte, $stock);

    return $panier;
}

/** Fixe la quantité (bornée au stock) ; 0 ou moins retire la ligne. */
function panier_modifier(array $panier, int $id, int $qte, int $stock): array
{
    if ($qte <= 0 || $stock <= 0) {
        return panier_retirer($panier, $id);
    }

    $panier[$id] = min($qte, $stock);

    return $panier;
}

function panier_retirer(array $panier, int $id): array
{
    unset($panier[$id]);

    return $panier;
}

/** Nombre total d'articles. */
function panier_nombre(array $panier): int
{
    return (int) array_sum($panier);
}

/**
 * Détail du panier avec les prix et stocks actuels de la base.
 * Les produits disparus sont ignorés ; une quantité trop grande est ramenée au stock.
 * @return array{lignes: list<array>, total: float}
 */
function panier_detail(array $panier): array
{
    $ids = array_values(array_filter(array_map('intval', array_keys($panier)), fn (int $id) => $id > 0));
    if ($ids === []) {
        return ['lignes' => [], 'total' => 0.0];
    }

    $marques = implode(', ', array_fill(0, count($ids), '?'));
    $produits = db_tous(
        SQL_PRODUITS . " WHERE p.id_produit IN ($marques) ORDER BY p.nom",
        str_repeat('i', count($ids)),
        $ids
    );

    $lignes = [];
    $total = 0.0;
    foreach (array_map('completer_produit', $produits) as $p) {
        $quantite = min(max((int) $panier[$p['id_produit']], 0), $p['quantite']);
        $sousTotal = round($quantite * $p['prix'], 2);
        $total += $sousTotal;

        $lignes[] = [
            'id_produit' => $p['id_produit'],
            'nom' => $p['nom'],
            'photo_nom' => $p['photo_nom'],
            'prix' => $p['prix'],
            'quantite' => $quantite,
            'stock' => $p['quantite'],
            'sous_total' => $sousTotal,
            'univers' => $p['univers'],
            'libelle_categorie' => $p['libelle_categorie'],
        ];
    }

    return ['lignes' => $lignes, 'total' => round($total, 2)];
}

/** Stock actuel d'un produit, ou null s'il n'existe pas. */
function stock_produit(int $id): ?int
{
    $ligne = db_un('SELECT quantite FROM PRODUIT WHERE id_produit = ?', 'i', [$id]);

    return $ligne === null ? null : (int) $ligne['quantite'];
}
