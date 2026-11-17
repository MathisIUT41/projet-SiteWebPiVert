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
    public function test_carte_produit_echappe_le_json(): void
    {
        $p = ["id_produit" => 1, "nom" => "\"><script>x</script>", "prix" => 1.0, "quantite" => 1, "photo_nom" => null, "libelle_categorie" => "A", "slug_categorie" => "a", "espece" => null, "libelle_espece" => null, "univers" => "vegetal", "description" => "", "caracteristiques" => []];
        $html = rendu_chaine("partials/carte-produit.twig", ["produit" => $p]);
        $this->assertStringNotContainsString("<script>x", $html);
        $this->assertStringContainsString("data-produit=", $html);
    }

    public function test_modale_produit_presente_sur_la_page_univers(): void
    {
        $html = rendu_chaine("pages/univers.twig", ["univers" => "vegetal", "titre_univers" => "Végétal", "filtres" => [], "filtre_actif" => null, "produits" => [], "conseils" => []]);
        $this->assertStringContainsString("id=\"modale-produit\"", $html);
        $this->assertStringContainsString("modale-produit.js", $html);
    }
    public function test_bandeau_demo(): void
    {
        $_SESSION["client"] = ["prenom" => "Recruteur", "role" => "demo"];
        $html = rendu_chaine("admin/tableau-de-bord.twig", ["stats" => ["total" => 0, "par_univers" => ["vegetal" => 0, "animalier" => 0], "stock_faible" => 0, "rupture" => 0], "produits" => [], "categories" => [], "base_url" => "../"]);
        $this->assertStringContainsString("Mode démo — lecture seule", $html);
    }

    public function test_pas_de_bandeau_pour_admin(): void
    {
        $_SESSION["client"] = ["prenom" => "Mathis", "role" => "admin"];
        $html = rendu_chaine("admin/tableau-de-bord.twig", ["stats" => ["total" => 0, "par_univers" => ["vegetal" => 0, "animalier" => 0], "stock_faible" => 0, "rupture" => 0], "produits" => [], "categories" => [], "base_url" => "../"]);
        $this->assertStringNotContainsString("Mode démo — lecture seule", $html);
    }
}
