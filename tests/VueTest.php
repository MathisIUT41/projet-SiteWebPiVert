<?php

declare(strict_types=1);

final class VueTest extends BaseTestCase
{
    public function test_body_porte_le_theme_d_univers(): void
    {
        $html = rendu_chaine('base.twig', ['univers' => 'animalier']);
        $this->assertStringContainsString('data-univers="animalier"', $html);
    }

    public function test_badge_panier_somme_des_quantites(): void
    {
        $_SESSION['panier'] = [3 => 2, 8 => 1];
        $this->assertMatchesRegularExpression('/class="nav-panier-nombre"[^>]*>3</', rendu_chaine('base.twig'));
    }

    public function test_flash_affiche_puis_consomme(): void
    {
        flash('succes', 'Ajouté au panier');
        $this->assertStringContainsString('Ajouté au panier', rendu_chaine('base.twig'));
        $this->assertSame([], flashs_lire());
    }

    public function test_filtre_prix(): void
    {
        $this->assertSame("24,90\u{202F}€", twig()->createTemplate('{{ 24.9|prix }}')->render());
    }

    public function test_url_retour_sure_refuse_les_urls_externes(): void
    {
        $this->assertSame('index.php', url_retour_sure('https://evil.test'));
        $this->assertSame('index.php', url_retour_sure('//evil.test'));
        $this->assertSame('index.php', url_retour_sure('/\evil.test'));
        $this->assertSame('panier.php', url_retour_sure('panier.php'));
        $this->assertSame('univers.php?u=vegetal', url_retour_sure('univers.php?u=vegetal'));
    }
}
