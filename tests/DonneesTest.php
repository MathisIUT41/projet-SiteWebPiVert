<?php

declare(strict_types=1);

final class DonneesTest extends BaseTestCase
{
    public static function setUpBeforeClass(): void
    {
        self::reinstallerBase();
    }

    public function test_51_produits_et_10_categories_reparties(): void
    {
        $this->assertSame(51, (int) db_un('SELECT COUNT(*) n FROM PRODUIT')['n']);
        $parUnivers = array_column(db_tous('SELECT univers, COUNT(*) n FROM CATEGORIE GROUP BY univers'), 'n', 'univers');
        $this->assertEquals(['animalier' => 5, 'vegetal' => 5], $parUnivers);
    }

    public function test_chaque_photo_existe_sur_le_disque(): void
    {
        foreach (db_tous('SELECT photo_nom FROM PRODUIT WHERE photo_nom IS NOT NULL') as $p) {
            $this->assertFileExists(__DIR__ . '/../public/images/produits/' . $p['photo_nom']);
        }
    }

    public function test_espece_seulement_sur_produits_animaliers(): void
    {
        $this->assertSame(0, (int) db_un("SELECT COUNT(*) n FROM PRODUIT p JOIN CATEGORIE c USING(id_categorie) WHERE c.univers='vegetal' AND p.espece IS NOT NULL")['n']);
        $this->assertSame(2, (int) db_un("SELECT COUNT(*) n FROM PRODUIT p JOIN CATEGORIE c USING(id_categorie) WHERE c.univers='animalier' AND p.espece IS NULL")['n']);
    }

    public function test_deux_a_quatre_caracteristiques_par_produit(): void
    {
        $hors = db_un('SELECT COUNT(*) n FROM (SELECT p.id_produit, COUNT(c.id_caracteristique) k FROM PRODUIT p LEFT JOIN CARACTERISTIQUE c USING(id_produit) GROUP BY p.id_produit HAVING k < 2 OR k > 4) t');
        $this->assertSame(0, (int) $hors['n']);
    }

    public function test_comptes_de_demo_haches_et_aucun_admin(): void
    {
        $demo = db_un("SELECT * FROM CLIENT WHERE email = 'recruteur@pivert.demo'");
        $this->assertSame('demo', $demo['role']);
        $this->assertTrue(password_verify('recruteur2027', $demo['mot_de_passe']));
        $this->assertSame(0, (int) db_un("SELECT COUNT(*) n FROM CLIENT WHERE role = 'admin'")['n']);
    }
}
