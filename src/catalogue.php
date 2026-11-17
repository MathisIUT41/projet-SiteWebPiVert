<?php

declare(strict_types=1);

const UNIVERS = ['vegetal' => 'Végétal', 'animalier' => 'Animalier'];

const ESPECES = [
    'chiens' => 'Chiens',
    'chats' => 'Chats',
    'rongeurs' => 'Rongeurs',
    'oiseaux' => 'Oiseaux',
    'poissons' => 'Poissons',
    'autres' => 'Autres animaux',
];

/** Colonnes communes à toutes les listes de produits. */
const SQL_PRODUITS = 'SELECT p.id_produit, p.nom, p.description, p.prix, p.quantite, p.photo_nom, p.espece, p.date_ajout,
           c.slug AS slug_categorie, c.libelle AS libelle_categorie, c.univers
    FROM PRODUIT p
    JOIN CATEGORIE c ON c.id_categorie = p.id_categorie';

/** Types homogènes + libellé d'espèce pour l'affichage. */
function completer_produit(array $p): array
{
    $p['id_produit'] = (int) $p['id_produit'];
    $p['prix'] = (float) $p['prix'];
    $p['quantite'] = (int) $p['quantite'];
    $p['libelle_espece'] = $p['univers'] === 'animalier'
        ? (ESPECES[$p['espece']] ?? 'Tous animaux')
        : null;

    return $p;
}

/**
 * Filtres proposés sur la page d'un univers (slug ⇒ libellé) :
 * catégories pour le végétal, espèces pour l'animalier.
 */
function filtres_univers(string $univers): array
{
    if ($univers === 'animalier') {
        return ESPECES;
    }

    $categories = db_tous(
        'SELECT slug, libelle FROM CATEGORIE WHERE univers = ? ORDER BY id_categorie',
        's',
        [$univers]
    );

    return array_column($categories, 'libelle', 'slug');
}

/** Le filtre s'il existe pour cet univers, sinon null (« Tout »). */
function filtre_valide(string $univers, ?string $filtre): ?string
{
    if ($filtre === null || !isset(UNIVERS[$univers])) {
        return null;
    }

    return array_key_exists($filtre, filtres_univers($univers)) ? $filtre : null;
}

/** Produits d'un univers, éventuellement filtrés, triés par nom. */
function produits_univers(string $univers, ?string $filtre): array
{
    $sql = SQL_PRODUITS . ' WHERE c.univers = ?';
    $types = 's';
    $params = [$univers];

    if ($filtre !== null && $univers === 'vegetal') {
        $sql .= ' AND c.slug = ?';
        $types .= 's';
        $params[] = $filtre;
    } elseif ($filtre !== null && $univers === 'animalier') {
        // Les produits « tous animaux » (espece NULL) apparaissent dans chaque filtre.
        $sql .= ' AND (p.espece = ? OR p.espece IS NULL)';
        $types .= 's';
        $params[] = $filtre;
    }

    return array_map('completer_produit', db_tous($sql . ' ORDER BY p.nom', $types, $params));
}

/**
 * Caractéristiques de plusieurs produits en une requête.
 * @return array<int, list<array{libelle: string, valeur: string, icone: string}>>
 */
function caracteristiques_de(array $idsProduits): array
{
    $ids = array_values(array_unique(array_map('intval', $idsProduits)));
    if ($ids === []) {
        return [];
    }

    $marques = implode(', ', array_fill(0, count($ids), '?'));
    $lignes = db_tous(
        "SELECT id_produit, libelle, valeur, icone FROM CARACTERISTIQUE
         WHERE id_produit IN ($marques) ORDER BY id_produit, ordre",
        str_repeat('i', count($ids)),
        $ids
    );

    $parProduit = [];
    foreach ($lignes as $l) {
        $parProduit[(int) $l['id_produit']][] = [
            'libelle' => $l['libelle'],
            'valeur' => $l['valeur'],
            'icone' => $l['icone'],
        ];
    }

    return $parProduit;
}

/** Ajoute la clé « caracteristiques » à chaque produit (liste vide si aucune). */
function ajouter_caracteristiques(array $produits): array
{
    $caracteristiques = caracteristiques_de(array_column($produits, 'id_produit'));

    return array_map(
        fn (array $p) => $p + ['caracteristiques' => $caracteristiques[$p['id_produit']] ?? []],
        $produits
    );
}

/**
 * Produits mis en avant sur l'accueil : les plus récents en stock,
 * moitié végétal, moitié animalier, en alternance.
 */
function produits_mis_en_avant(int $n = 4): array
{
    $parUnivers = [];
    foreach (array_keys(UNIVERS) as $univers) {
        $parUnivers[$univers] = array_map('completer_produit', db_tous(
            SQL_PRODUITS . ' WHERE c.univers = ? AND p.quantite > 0 ORDER BY p.date_ajout DESC, p.id_produit DESC LIMIT ?',
            'si',
            [$univers, intdiv($n + 1, 2)]
        ));
    }

    $resultat = [];
    for ($i = 0; count($resultat) < $n && ($i < count($parUnivers['vegetal']) || $i < count($parUnivers['animalier'])); $i++) {
        foreach (['vegetal', 'animalier'] as $univers) {
            if (isset($parUnivers[$univers][$i]) && count($resultat) < $n) {
                $resultat[] = $parUnivers[$univers][$i];
            }
        }
    }

    return $resultat;
}

/**
 * Chiffres du tableau de bord en une requête.
 * Stock faible = 1 à 5 exemplaires ; rupture = 0.
 */
function stats_stock(): array
{
    $l = db_un(
        "SELECT COUNT(*) AS total,
                SUM(c.univers = 'vegetal') AS vegetal,
                SUM(c.univers = 'animalier') AS animalier,
                SUM(p.quantite BETWEEN 1 AND 5) AS stock_faible,
                SUM(p.quantite = 0) AS rupture
         FROM PRODUIT p JOIN CATEGORIE c ON c.id_categorie = p.id_categorie"
    );

    return [
        'total' => (int) $l['total'],
        'par_univers' => ['vegetal' => (int) $l['vegetal'], 'animalier' => (int) $l['animalier']],
        'stock_faible' => (int) $l['stock_faible'],
        'rupture' => (int) $l['rupture'],
    ];
}

/** Tous les produits pour le tableau de l'admin, avec marque. */
function produits_admin(): array
{
    $lignes = db_tous(
        'SELECT p.id_produit, p.nom, p.description, p.prix, p.quantite, p.photo_nom, p.espece, p.date_ajout,
                c.id_categorie, c.slug AS slug_categorie, c.libelle AS libelle_categorie, c.univers,
                m.nom_marque
         FROM PRODUIT p
         JOIN CATEGORIE c ON c.id_categorie = p.id_categorie
         LEFT JOIN MARQUE m ON m.id_marque = p.id_marque
         ORDER BY p.nom'
    );

    return array_map('completer_produit', $lignes);
}

/** Catégories (pour les listes déroulantes de l'admin). */
function categories(): array
{
    return db_tous('SELECT id_categorie, slug, libelle, univers FROM CATEGORIE ORDER BY id_categorie');
}

/** Marques (pour le formulaire produit). */
function marques(): array
{
    return db_tous('SELECT id_marque, nom_marque FROM MARQUE ORDER BY nom_marque');
}
