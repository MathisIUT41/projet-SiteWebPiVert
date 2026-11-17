<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/src/bootstrap.php';

exiger_admin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$produit = null;

if ($id !== null) {
    $produit = db_un('SELECT * FROM PRODUIT WHERE id_produit = ?', 'i', [$id]);
    if ($produit === null) {
        flash('erreur', 'Ce produit n\'existe pas.');
        rediriger('index.php');
    }
}

$erreurs = [];
$saisie = $produit === null
    ? ['nom' => '', 'description' => '', 'prix' => '', 'quantite' => '0', 'id_categorie' => '', 'id_marque' => '', 'espece' => '', 'caracteristiques' => []]
    : [
        'nom' => $produit['nom'],
        'description' => $produit['description'],
        'prix' => number_format((float) $produit['prix'], 2, ',', ''),
        'quantite' => (string) $produit['quantite'],
        'id_categorie' => (string) $produit['id_categorie'],
        'id_marque' => (string) ($produit['id_marque'] ?? ''),
        'espece' => (string) ($produit['espece'] ?? ''),
        'caracteristiques' => caracteristiques_de([$id])[$id] ?? [],
    ];

if (est_post()) {
    exiger_ecriture_admin();
    exiger_csrf();

    ['donnees' => $donnees, 'erreurs' => $erreurs] = valider_produit($_POST);
    $photo = valider_photo($_FILES['photo'] ?? ['error' => UPLOAD_ERR_NO_FILE]);
    if (!$photo['ok']) {
        $erreurs['photo'] = $photo['erreur'];
    }

    if ($erreurs === []) {
        $photoNom = null;
        if ($photo['extension'] !== null) {
            $photoNom = 'upload-' . bin2hex(random_bytes(8)) . '.' . $photo['extension'];
            if (!move_uploaded_file($_FILES['photo']['tmp_name'], dirname(__DIR__) . '/images/produits/' . $photoNom)) {
                $erreurs['photo'] = 'L\'envoi de l\'image a échoué, réessaie.';
                $photoNom = null;
            }
        }

        if ($erreurs === []) {
            $ancienne = $produit['photo_nom'] ?? null;
            produit_enregistrer($donnees, $id, $photoNom);

            // L'ancienne photo envoyée depuis l'admin est remplacée : on l'efface.
            if ($photoNom !== null && is_string($ancienne) && str_starts_with($ancienne, 'upload-')) {
                @unlink(dirname(__DIR__) . '/images/produits/' . basename($ancienne));
            }

            flash('succes', 'Produit enregistré');
            rediriger('index.php');
        }
    }

    // Formulaire ré-affiché avec ce qui a été saisi.
    $saisie = [
        'nom' => (string) ($_POST['nom'] ?? ''),
        'description' => (string) ($_POST['description'] ?? ''),
        'prix' => (string) ($_POST['prix'] ?? ''),
        'quantite' => (string) ($_POST['quantite'] ?? ''),
        'id_categorie' => (string) ($_POST['id_categorie'] ?? ''),
        'id_marque' => (string) ($_POST['id_marque'] ?? ''),
        'espece' => (string) ($_POST['espece'] ?? ''),
        'caracteristiques' => $donnees['caracteristiques'],
    ];
}

rendu_admin('admin/produit-form.twig', [
    'page_admin' => $id === null ? 'produit' : 'tableau',
    'produit' => $produit,
    'saisie' => $saisie,
    'erreurs' => $erreurs,
    'categories' => categories(),
    'marques' => marques(),
    'especes' => ESPECES,
    'icones' => ICONES_CARACTERISTIQUES,
]);
