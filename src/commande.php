<?php

declare(strict_types=1);

/** Levée quand un produit n'a pas (ou plus) assez de stock pour la commande. */
final class StockInsuffisant extends RuntimeException
{
}

/**
 * Crée une commande à partir d'un panier [id_produit => quantité], dans une transaction :
 * stock vérifié et décrémenté, prix pris en base. Tout est annulé à la moindre erreur.
 */
function commande_creer(int $idClient, array $panier): int
{
    $lignes = array_filter(
        array_map('intval', $panier),
        fn (int $qte, $id) => (int) $id > 0 && $qte > 0,
        ARRAY_FILTER_USE_BOTH
    );
    if ($lignes === []) {
        throw new InvalidArgumentException('Le panier est vide.');
    }

    $cnx = db();
    $cnx->begin_transaction();

    try {
        $total = 0.0;
        $details = [];

        foreach ($lignes as $id => $qte) {
            $produit = db_un('SELECT nom, prix, quantite FROM PRODUIT WHERE id_produit = ? FOR UPDATE', 'i', [(int) $id]);
            if ($produit === null) {
                throw new StockInsuffisant('Un produit de ton panier n\'existe plus.');
            }

            $modifiees = db_executer(
                'UPDATE PRODUIT SET quantite = quantite - ? WHERE id_produit = ? AND quantite >= ?',
                'iii',
                [$qte, (int) $id, $qte]
            );
            if ($modifiees === 0) {
                throw new StockInsuffisant("Stock insuffisant pour « {$produit['nom']} ».");
            }

            $details[] = [(int) $id, $qte, (float) $produit['prix']];
            $total += $qte * (float) $produit['prix'];
        }

        db_executer(
            "INSERT INTO COMMANDE (id_client, date_commande, total, statut) VALUES (?, CURDATE(), ?, 'validée')",
            'id',
            [$idClient, round($total, 2)]
        );
        $idCommande = $cnx->insert_id;

        foreach ($details as [$id, $qte, $prix]) {
            db_executer(
                'INSERT INTO COMPORTER (id_produit, id_commande, quantite, prix_unitaire) VALUES (?, ?, ?, ?)',
                'iiid',
                [$id, $idCommande, $qte, $prix]
            );
        }

        $cnx->commit();

        return $idCommande;
    } catch (Throwable $e) {
        $cnx->rollback();
        throw $e;
    }
}

/** Commande complète (en-tête, lignes, client), ou null si elle n'existe pas. */
function commande_par_id(int $idCommande): ?array
{
    $commande = db_un('SELECT * FROM COMMANDE WHERE id_commande = ?', 'i', [$idCommande]);
    if ($commande === null) {
        return null;
    }

    $lignes = db_tous(
        'SELECT co.id_produit, co.quantite, co.prix_unitaire, p.nom, p.photo_nom, c.univers
         FROM COMPORTER co
         JOIN PRODUIT p ON p.id_produit = co.id_produit
         JOIN CATEGORIE c ON c.id_categorie = p.id_categorie
         WHERE co.id_commande = ?
         ORDER BY p.nom',
        'i',
        [$idCommande]
    );

    $client = db_un(
        'SELECT id_client, prenom, nom, adresse, email FROM CLIENT WHERE id_client = ?',
        'i',
        [(int) $commande['id_client']]
    );

    return ['commande' => $commande, 'lignes' => $lignes, 'client' => $client];
}
