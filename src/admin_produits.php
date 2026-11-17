<?php

declare(strict_types=1);

const ICONES_CARACTERISTIQUES = ['soleil', 'goutte', 'regle', 'thermometre', 'sablier', 'assiette', 'maison', 'patte', 'feuille', 'outil'];
const PHOTO_TAILLE_MAX = 3 * 1024 * 1024;
const PHOTO_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

/**
 * Vérifie le formulaire produit de l'admin.
 * @return array{donnees: array, erreurs: array<string, string>}
 */
function valider_produit(array $post): array
{
    $erreurs = [];
    $nom = trim(en_texte($post['nom'] ?? ''));
    $description = trim(en_texte($post['description'] ?? ''));
    $prixSaisi = str_replace([' ', "\u{202F}", ','], ['', '', '.'], trim(en_texte($post['prix'] ?? '')));
    $quantite = filter_var($post['quantite'] ?? null, FILTER_VALIDATE_INT);
    $idCategorie = filter_var($post['id_categorie'] ?? null, FILTER_VALIDATE_INT);
    $idMarque = trim(en_texte($post['id_marque'] ?? ''));
    $espece = trim(en_texte($post['espece'] ?? ''));

    if ($nom === '' || mb_strlen($nom) > 255) {
        $erreurs['nom'] = 'Le nom est obligatoire (255 caractères maximum).';
    }
    if ($description === '' || mb_strlen($description) > 255) {
        $erreurs['description'] = 'La description est obligatoire (255 caractères maximum).';
    }
    if (!is_numeric($prixSaisi) || (float) $prixSaisi <= 0) {
        $erreurs['prix'] = 'Indique un prix supérieur à 0 (ex. : 12,90).';
    }
    if ($quantite === false || $quantite < 0) {
        $erreurs['quantite'] = 'Le stock doit être un nombre entier, 0 ou plus.';
    }

    $categorie = $idCategorie === false ? null
        : db_un('SELECT univers FROM CATEGORIE WHERE id_categorie = ?', 'i', [$idCategorie]);
    if ($categorie === null) {
        $erreurs['id_categorie'] = 'Choisis une catégorie.';
    }

    $marque = null;
    if ($idMarque !== '') {
        $marque = filter_var($idMarque, FILTER_VALIDATE_INT);
        if ($marque === false || db_un('SELECT id_marque FROM MARQUE WHERE id_marque = ?', 'i', [$marque]) === null) {
            $erreurs['id_marque'] = 'Cette marque n\'existe pas.';
        }
    }

    if ($espece !== '' && !array_key_exists($espece, ESPECES)) {
        $erreurs['espece'] = 'Choisis un animal dans la liste.';
    }
    if (($categorie['univers'] ?? null) !== 'animalier' || $espece === '') {
        $espece = null;
    }

    $caracteristiques = [];
    $lignes = is_array($post['caracteristiques'] ?? null) ? $post['caracteristiques'] : [];
    foreach ($lignes as $ligne) {
        if (!is_array($ligne)) {
            continue;
        }
        $libelle = trim(en_texte($ligne['libelle'] ?? ''));
        $valeur = trim(en_texte($ligne['valeur'] ?? ''));
        $icone = trim(en_texte($ligne['icone'] ?? ''));
        if ($libelle === '' && $valeur === '') {
            continue;
        }
        if ($libelle === '' || $valeur === '' || mb_strlen($libelle) > 60 || mb_strlen($valeur) > 60
            || !in_array($icone, ICONES_CARACTERISTIQUES, true)) {
            $erreurs['caracteristiques'] = 'Chaque caractéristique a besoin d\'un libellé, d\'une valeur (60 caractères maximum) et d\'une icône.';
        }
        $caracteristiques[] = ['libelle' => $libelle, 'valeur' => $valeur, 'icone' => $icone];
    }
    if (count($caracteristiques) > 6) {
        $erreurs['caracteristiques'] = '6 caractéristiques maximum.';
    }

    return [
        'donnees' => [
            'nom' => $nom,
            'description' => $description,
            'prix' => is_numeric($prixSaisi) ? round((float) $prixSaisi, 2) : 0.0,
            'quantite' => $quantite === false ? 0 : $quantite,
            'id_categorie' => $idCategorie === false ? 0 : $idCategorie,
            'id_marque' => $marque === false ? null : $marque,
            'espece' => $espece,
            'caracteristiques' => $caracteristiques,
        ],
        'erreurs' => $erreurs,
    ];
}

/**
 * Vérifie une photo envoyée ($_FILES['photo']) : type réel, taille.
 * @return array{ok: bool, erreur: ?string, extension: ?string}
 */
function valider_photo(array $fichier): array
{
    $erreurUpload = $fichier['error'] ?? UPLOAD_ERR_NO_FILE;
    if ($erreurUpload === UPLOAD_ERR_NO_FILE) {
        return ['ok' => true, 'erreur' => null, 'extension' => null];
    }
    if ($erreurUpload === UPLOAD_ERR_INI_SIZE || $erreurUpload === UPLOAD_ERR_FORM_SIZE
        || (int) ($fichier['size'] ?? 0) > PHOTO_TAILLE_MAX) {
        return ['ok' => false, 'erreur' => 'Image trop lourde (3 Mo maximum).', 'extension' => null];
    }
    if ($erreurUpload !== UPLOAD_ERR_OK || !is_file((string) ($fichier['tmp_name'] ?? ''))) {
        return ['ok' => false, 'erreur' => 'L\'envoi de l\'image a échoué, réessaie.', 'extension' => null];
    }

    $type = (new finfo(FILEINFO_MIME_TYPE))->file($fichier['tmp_name']);
    if (!isset(PHOTO_TYPES[$type])) {
        return ['ok' => false, 'erreur' => 'Format accepté : JPG, PNG ou WebP.', 'extension' => null];
    }

    return ['ok' => true, 'erreur' => null, 'extension' => PHOTO_TYPES[$type]];
}

/** Crée (id null) ou met à jour un produit et ses caractéristiques. Renvoie son id. */
function produit_enregistrer(array $donnees, ?int $id, ?string $photoNom): int
{
    $cnx = db();
    $cnx->begin_transaction();

    try {
        $valeurs = [
            $donnees['id_categorie'], $donnees['id_marque'], $donnees['nom'], $donnees['description'],
            $donnees['prix'], $donnees['quantite'], $donnees['espece'],
        ];

        if ($id === null) {
            db_executer(
                'INSERT INTO PRODUIT (id_categorie, id_marque, nom, description, prix, quantite, espece, photo_nom, date_ajout)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURDATE())',
                'iissdiss',
                [...$valeurs, $photoNom]
            );
            $id = $cnx->insert_id;
        } else {
            db_executer(
                'UPDATE PRODUIT SET id_categorie = ?, id_marque = ?, nom = ?, description = ?, prix = ?, quantite = ?, espece = ?
                 WHERE id_produit = ?',
                'iissdisi',
                [...$valeurs, $id]
            );
            if ($photoNom !== null) {
                db_executer('UPDATE PRODUIT SET photo_nom = ? WHERE id_produit = ?', 'si', [$photoNom, $id]);
            }
        }

        caracteristiques_remplacer($id, $donnees['caracteristiques']);
        $cnx->commit();

        return $id;
    } catch (Throwable $e) {
        $cnx->rollback();
        throw $e;
    }
}

/** Remplace toutes les caractéristiques d'un produit (l'ordre suit la liste). */
function caracteristiques_remplacer(int $idProduit, array $lignes): void
{
    db_executer('DELETE FROM CARACTERISTIQUE WHERE id_produit = ?', 'i', [$idProduit]);

    foreach (array_values($lignes) as $ordre => $l) {
        db_executer(
            'INSERT INTO CARACTERISTIQUE (id_produit, libelle, valeur, icone, ordre) VALUES (?, ?, ?, ?, ?)',
            'isssi',
            [$idProduit, $l['libelle'], $l['valeur'], $l['icone'], $ordre]
        );
    }
}

/**
 * Supprime un produit. Refusé (false) s'il figure dans des commandes :
 * il faut alors passer son stock à 0 pour garder l'historique.
 */
function produit_supprimer(int $id): bool
{
    if (db_un('SELECT 1 AS x FROM COMPORTER WHERE id_produit = ? LIMIT 1', 'i', [$id]) !== null) {
        return false;
    }

    $photo = db_un('SELECT photo_nom FROM PRODUIT WHERE id_produit = ?', 'i', [$id])['photo_nom'] ?? null;
    if (db_executer('DELETE FROM PRODUIT WHERE id_produit = ?', 'i', [$id]) === 0) {
        return false;
    }

    // Seules les photos envoyées depuis l'admin sont effacées (pas les photos de démonstration).
    if (is_string($photo) && str_starts_with($photo, 'upload-')) {
        $chemin = dirname(__DIR__) . '/public/images/produits/' . basename($photo);
        if (is_file($chemin)) {
            unlink($chemin);
        }
    }

    return true;
}
