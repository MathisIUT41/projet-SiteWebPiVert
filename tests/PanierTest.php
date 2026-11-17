<?php

declare(strict_types=1);

final class PanierTest extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        self::reinstallerBase();
    }

    public function test_ajouter_borne_quantite(): void
    {
        $this->assertSame([5 => 3], panier_ajouter([5 => 2], 5, 4, 3));
        $this->assertSame([5 => 1], panier_ajouter([], 5, 1, 10));
        $this->assertSame([], panier_ajouter([], 5, -3, 10));
        $this->assertSame([], panier_ajouter([], 5, 0, 10));
        $this->assertSame([], panier_ajouter([], 5, 1, 0));
    }

    public function test_modifier_a_zero_retire(): void
    {
        $this->assertSame([2 => 1], panier_modifier([5 => 2, 2 => 1], 5, 0, 10));
        $this->assertSame([5 => 10], panier_modifier([5 => 2], 5, 99999, 10));
        $this->assertSame([5 => 4], panier_modifier([5 => 2], 5, 4, 10));
    }

    public function test_retirer(): void
    {
        $this->assertSame([2 => 1], panier_retirer([5 => 2, 2 => 1], 5));
        $this->assertSame([2 => 1], panier_retirer([2 => 1], 99));
    }

    public function test_nombre(): void
    {
        $this->assertSame(3, panier_nombre([3 => 2, 8 => 1]));
        $this->assertSame(0, panier_nombre([]));
    }

    public function test_detail_ignore_produit_inexistant(): void
    {
        $d = panier_detail([1 => 2, 999999 => 1]);
        $this->assertCount(1, $d['lignes']);
        $prix1 = (float) db_un('SELECT prix FROM PRODUIT WHERE id_produit = 1')['prix'];
        $this->assertEqualsWithDelta(2 * $prix1, $d['total'], 0.001);
        $this->assertSame([], panier_detail([])['lignes']);
    }

    public function test_detail_ramene_au_stock(): void
    {
        db_executer('UPDATE PRODUIT SET quantite = 1 WHERE id_produit = 1');
        $this->assertSame(1, panier_detail([1 => 5])['lignes'][0]['quantite']);
    }

    public function test_detail_ligne_en_rupture(): void
    {
        db_executer('UPDATE PRODUIT SET quantite = 0 WHERE id_produit = 1');
        $d = panier_detail([1 => 2, 2 => 1]);
        $parId = array_column($d['lignes'], null, 'id_produit');
        $this->assertSame(0, $parId[1]['quantite']);
        $this->assertEqualsWithDelta(0.0, $parId[1]['sous_total'], 0.001);
        $this->assertEqualsWithDelta((float) db_un('SELECT prix FROM PRODUIT WHERE id_produit = 2')['prix'], $d['total'], 0.001);
    }
}
