<?php

declare(strict_types=1);

final class AdminTest extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        self::reinstallerBase();
    }

    public function test_demo_ne_peut_pas_ecrire(): void
    {
        $this->assertFalse(ecriture_admin_autorisee(['role' => 'demo']));
        $this->assertFalse(ecriture_admin_autorisee(['role' => 'client']));
        $this->assertFalse(ecriture_admin_autorisee(null));
        $this->assertTrue(ecriture_admin_autorisee(['role' => 'admin']));
    }

    public function test_stats_stock(): void
    {
        db_executer('UPDATE PRODUIT SET quantite = 0 WHERE id_produit = 1');
        db_executer('UPDATE PRODUIT SET quantite = 3 WHERE id_produit = 2');
        $s = stats_stock();
        $this->assertSame(51, $s['total']);
        $this->assertSame(['vegetal' => 26, 'animalier' => 25], $s['par_univers']);
        $this->assertSame(1, $s['rupture']);
        $this->assertGreaterThanOrEqual(1, $s['stock_faible']);
    }

    public function test_produits_admin(): void
    {
        $p = produits_admin();
        $this->assertCount(51, $p);
        $parNom = array_column($p, null, 'nom');
        $this->assertSame('Stihl', $parNom['Tondeuse']['nom_marque']);
        $this->assertSame('Chiens', $parNom['Laisse pour chien']['libelle_espece']);
    }
    public function test_refus_admin_sans_boucle_de_redirection(): void
    {
        $this->assertSame("../compte.php?retour=admin/index.php", destination_refus_admin(null));
        $this->assertSame("../index.php", destination_refus_admin(["role" => "client"]));
    }
}
