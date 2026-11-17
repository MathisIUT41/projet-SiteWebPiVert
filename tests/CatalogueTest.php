<?php

declare(strict_types=1);

final class CatalogueTest extends BaseTestCase
{
    public static function setUpBeforeClass(): void
    {
        self::reinstallerBase();
    }

    public function test_filtre_chats_inclut_les_produits_tous_animaux(): void
    {
        $noms = array_column(produits_univers('animalier', 'chats'), 'nom');
        $this->assertContains('Croquettes pour chat', $noms);
        $this->assertContains('Fontaine à eau pour animaux', $noms);
        $this->assertNotContains('Laisse pour chien', $noms);
    }

    public function test_filtre_categorie_vegetal(): void
    {
        $this->assertSame(['fleurs'], array_values(array_unique(array_column(produits_univers('vegetal', 'fleurs'), 'slug_categorie'))));
    }

    public function test_filtre_invalide_ignore(): void
    {
        $this->assertNull(filtre_valide('animalier', 'licornes'));
        $this->assertNull(filtre_valide('vegetal', 'chats'));
        $this->assertNull(filtre_valide('vegetal', null));
        $this->assertSame('chats', filtre_valide('animalier', 'chats'));
        $this->assertSame('arbres', filtre_valide('vegetal', 'arbres'));
        $this->assertCount(25, produits_univers('animalier', null));
        $this->assertCount(26, produits_univers('vegetal', null));
    }

    public function test_filtres_univers(): void
    {
        $this->assertSame(['plantes', 'fleurs', 'arbres', 'outils-jardinage', 'decoration-jardin'], array_keys(filtres_univers('vegetal')));
        $this->assertSame('Autres animaux', filtres_univers('animalier')['autres']);
    }

    public function test_caracteristiques_groupees_par_produit(): void
    {
        $c = caracteristiques_de([1, 2]);
        $this->assertSame([1, 2], array_keys($c));
        $this->assertSame(['libelle' => 'Lumière', 'valeur' => 'Indirecte vive', 'icone' => 'soleil'], $c[1][0]);
        $this->assertSame([], caracteristiques_de([]));
    }

    public function test_conseils_et_conseil_du_jour_stable(): void
    {
        $this->assertNotEmpty(conseils('vegetal'));
        $this->assertNotEmpty(conseils('animalier'));
        $d = new DateTimeImmutable('2026-11-17');
        $this->assertSame(conseil_du_jour($d), conseil_du_jour($d));
        $this->assertArrayHasKey('titre', conseil_du_jour($d));
    }
}
