<?php

declare(strict_types=1);

final class CommandeTest extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        self::reinstallerBase();
    }

    private function stock(int $id): int
    {
        return (int) db_un('SELECT quantite q FROM PRODUIT WHERE id_produit = ?', 'i', [$id])['q'];
    }

    public function test_commande_decremente_le_stock(): void
    {
        $avant = $this->stock(1);
        $id = commande_creer($this->idClientDemo(), [1 => 2]);
        $this->assertSame($avant - 2, $this->stock(1));
        $commande = commande_par_id($id);
        $this->assertSame('validée', $commande['commande']['statut']);
        $this->assertCount(1, $commande['lignes']);
        $this->assertSame('Camille', $commande['client']['prenom']);
    }

    public function test_stock_insuffisant_annule_tout(): void
    {
        db_executer('UPDATE PRODUIT SET quantite = 1 WHERE id_produit = 2');
        $avant1 = $this->stock(1);
        try {
            commande_creer($this->idClientDemo(), [1 => 1, 2 => 5]);
            $this->fail('StockInsuffisant attendu');
        } catch (StockInsuffisant $e) {
            $this->assertStringContainsString('Ficus', $e->getMessage());
        }
        $this->assertSame(0, (int) db_un('SELECT COUNT(*) n FROM COMMANDE')['n']);
        $this->assertSame($avant1, $this->stock(1));
    }

    public function test_prix_pris_en_base(): void
    {
        $id = commande_creer($this->idClientDemo(), [1 => 1]);
        $prix = (float) db_un('SELECT prix FROM PRODUIT WHERE id_produit = 1')['prix'];
        $this->assertEqualsWithDelta($prix, (float) commande_par_id($id)['commande']['total'], 0.001);
    }

    public function test_panier_vide_refuse(): void
    {
        $this->expectException(InvalidArgumentException::class);
        commande_creer($this->idClientDemo(), []);
    }

    public function test_produit_inexistant_refuse(): void
    {
        $this->expectException(StockInsuffisant::class);
        commande_creer($this->idClientDemo(), [999999 => 1]);
    }

    public function test_commande_inconnue(): void
    {
        $this->assertNull(commande_par_id(424242));
    }
}
