<?php

declare(strict_types=1);

final class AdminProduitsTest extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        self::reinstallerBase();
    }

    private function saisie(array $champs = []): array
    {
        return $champs + [
            'nom' => 'Hamster', 'description' => 'Petit', 'prix' => '12', 'quantite' => '4',
            'id_categorie' => '6', 'id_marque' => '', 'espece' => 'rongeurs', 'caracteristiques' => [],
        ];
    }

    public function test_validation_produit(): void
    {
        $r = valider_produit(['nom' => '', 'description' => 'x', 'prix' => '-2', 'quantite' => '1.5', 'id_categorie' => '99', 'id_marque' => '', 'espece' => '', 'caracteristiques' => []]);
        $this->assertSame(['nom', 'prix', 'quantite', 'id_categorie'], array_keys($r['erreurs']));
    }

    public function test_espece_forcee_a_null_en_vegetal(): void
    {
        $r = valider_produit($this->saisie(['nom' => 'Rose', 'prix' => '4,50', 'quantite' => '3', 'id_categorie' => '2', 'espece' => 'chats']));
        $this->assertSame([], $r['erreurs']);
        $this->assertNull($r['donnees']['espece']);
        $this->assertSame(4.5, $r['donnees']['prix']);
    }

    public function test_espece_inconnue_et_icone_inconnue_refusees(): void
    {
        $r = valider_produit($this->saisie(['espece' => 'licornes', 'caracteristiques' => [['libelle' => 'A', 'valeur' => 'B', 'icone' => 'fusee']]]));
        $this->assertArrayHasKey('espece', $r['erreurs']);
        $this->assertArrayHasKey('caracteristiques', $r['erreurs']);
    }

    public function test_creation_avec_caracteristiques_puis_modification(): void
    {
        $d = valider_produit($this->saisie(['caracteristiques' => [
            ['libelle' => 'Vie', 'valeur' => '2 ans', 'icone' => 'sablier'],
            ['libelle' => '', 'valeur' => '', 'icone' => ''],
        ]]))['donnees'];
        $id = produit_enregistrer($d, null, null);
        $this->assertCount(1, caracteristiques_de([$id])[$id]);
        $this->assertSame('rongeurs', db_un('SELECT espece FROM PRODUIT WHERE id_produit = ?', 'i', [$id])['espece']);

        produit_enregistrer(['caracteristiques' => [], 'nom' => 'Hamster doré'] + $d, $id, null);
        $this->assertArrayNotHasKey($id, caracteristiques_de([$id]));
        $this->assertSame('Hamster doré', db_un('SELECT nom FROM PRODUIT WHERE id_produit = ?', 'i', [$id])['nom']);
    }

    public function test_modification_garde_la_photo_si_aucune_nouvelle(): void
    {
        $d = valider_produit($this->saisie())['donnees'];
        produit_enregistrer($d, 1, null);
        $this->assertSame('monstera.jpg', db_un('SELECT photo_nom FROM PRODUIT WHERE id_produit = 1')['photo_nom']);
        produit_enregistrer($d, 1, 'upload-abc.jpg');
        $this->assertSame('upload-abc.jpg', db_un('SELECT photo_nom FROM PRODUIT WHERE id_produit = 1')['photo_nom']);
    }

    public function test_suppression_refusee_si_commande(): void
    {
        commande_creer($this->idClientDemo(), [1 => 1]);
        $this->assertFalse(produit_supprimer(1));
        $this->assertTrue(produit_supprimer(2));
        $this->assertNull(db_un('SELECT id_produit FROM PRODUIT WHERE id_produit = 2'));
        $this->assertSame(0, (int) db_un('SELECT COUNT(*) n FROM CARACTERISTIQUE WHERE id_produit = 2')['n']);
    }

    public function test_photo_refuse_un_faux_jpg(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'pv');
        file_put_contents($tmp, '<?php echo 1;');
        $r = valider_photo(['name' => 'x.jpg', 'tmp_name' => $tmp, 'size' => 13, 'error' => UPLOAD_ERR_OK]);
        $this->assertFalse($r['ok']);
        $this->assertSame('Format accepté : JPG, PNG ou WebP.', $r['erreur']);
    }

    public function test_photo_accepte_un_vrai_jpg_et_aucun_fichier(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'pv');
        copy(__DIR__ . '/../public/images/produits/monstera.jpg', $tmp);
        $r = valider_photo(['name' => 'plante.png', 'tmp_name' => $tmp, 'size' => filesize($tmp), 'error' => UPLOAD_ERR_OK]);
        $this->assertTrue($r['ok']);
        $this->assertSame('jpg', $r['extension']);

        $vide = valider_photo(['name' => '', 'tmp_name' => '', 'size' => 0, 'error' => UPLOAD_ERR_NO_FILE]);
        $this->assertTrue($vide['ok']);
        $this->assertNull($vide['extension']);
    }

    public function test_photo_trop_lourde(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'pv');
        copy(__DIR__ . '/../public/images/produits/monstera.jpg', $tmp);
        $r = valider_photo(['name' => 'x.jpg', 'tmp_name' => $tmp, 'size' => 4 * 1024 * 1024, 'error' => UPLOAD_ERR_OK]);
        $this->assertSame('Image trop lourde (3 Mo maximum).', $r['erreur']);
    }
}
