<?php

declare(strict_types=1);

/** Corrections issues de la relecture finale de la branche. */
final class RevueFinaleTest extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        self::reinstallerBase();
    }

    // --- 1. Le compte démo ne doit pas exposer les données des vrais clients ---

    public function test_demo_ne_voit_que_les_clients_de_demo(): void
    {
        inscrire(['nom' => 'Réel', 'prenom' => 'Client', 'email' => 'vrai@exemple.fr', 'mot_de_passe' => 'motdepasse1', 'adresse' => '1 rue Vraie']);
        $pourDemo = array_column(clients_pour_commande(['role' => 'demo']), 'email');
        $this->assertNotContains('vrai@exemple.fr', $pourDemo);
        $this->assertContains('client@pivert.demo', $pourDemo);
        $this->assertContains('vrai@exemple.fr', array_column(clients_pour_commande(['role' => 'admin']), 'email'));
    }

    public function test_donnees_client_masquees_pour_demo(): void
    {
        $vrai = ['prenom' => 'Léa', 'nom' => 'Dupont', 'email' => 'lea@exemple.fr', 'adresse' => '1 rue A'];
        $masque = client_affichable($vrai, ['role' => 'demo']);
        $this->assertStringNotContainsString('lea@exemple.fr', implode(' ', $masque));
        $this->assertStringNotContainsString('1 rue A', implode(' ', $masque));
        $this->assertStringNotContainsString('Dupont', implode(' ', $masque));
        $this->assertSame($vrai, client_affichable($vrai, ['role' => 'admin']));
        $demo = ['prenom' => 'Camille', 'nom' => 'Martin', 'email' => 'client@pivert.demo', 'adresse' => '12 rue des Lilas'];
        $this->assertSame($demo, client_affichable($demo, ['role' => 'demo']));
    }

    public function test_confirmation_visible_par_le_proprietaire_ou_l_admin_seulement(): void
    {
        $commande = ['commande' => ['id_client' => 7]];
        $this->assertTrue(peut_voir_commande($commande, ['id_client' => 7, 'role' => 'client']));
        $this->assertTrue(peut_voir_commande($commande, ['id_client' => 1, 'role' => 'admin']));
        $this->assertFalse(peut_voir_commande($commande, ['id_client' => 2, 'role' => 'demo']));
        $this->assertFalse(peut_voir_commande($commande, ['id_client' => 2, 'role' => 'client']));
        $this->assertFalse(peut_voir_commande($commande, null));
    }

    // --- 2. Une saisie trafiquée ne doit jamais faire planter la page ---

    public function test_en_texte(): void
    {
        $this->assertSame('', en_texte(['a']));
        $this->assertSame('', en_texte(null));
        $this->assertSame('12', en_texte(12));
        $this->assertSame('abc', en_texte('abc'));
    }

    public function test_tableaux_en_parametres_ignores(): void
    {
        $this->assertSame('index.php', url_retour_sure(['x']));
        $this->assertFalse(csrf_valide(['x']));
        $r = valider_inscription(['nom' => ['x'], 'prenom' => 'A', 'email' => 'a@b.fr', 'mot_de_passe' => 'motdepasse1', 'adresse' => 'B']);
        $this->assertArrayHasKey('nom', $r['erreurs']);
        $p = valider_produit(['nom' => ['x'], 'description' => 'x', 'prix' => '1', 'quantite' => '1', 'id_categorie' => '1', 'id_marque' => [], 'espece' => [], 'caracteristiques' => 'pas un tableau']);
        $this->assertArrayHasKey('nom', $p['erreurs']);
    }

    public function test_inscription_limite_la_longueur(): void
    {
        $r = valider_inscription(['nom' => str_repeat('a', 256), 'prenom' => 'A', 'email' => 'a@b.fr', 'mot_de_passe' => 'motdepasse1', 'adresse' => str_repeat('b', 256)]);
        $this->assertSame(['nom', 'adresse'], array_keys($r['erreurs']));
    }

    public function test_page_erreur_sans_details_techniques(): void
    {
        $html = html_page_erreur();
        $this->assertStringContainsString('Une erreur est survenue', $html);
        $this->assertStringNotContainsString(dirname(__DIR__), $html);
    }

    // --- 3. La carte animalier affiche le type de produit (spec §6.2) ---

    public function test_carte_animalier_affiche_la_categorie(): void
    {
        $p = completer_produit(['id_produit' => 32, 'nom' => 'Croquettes pour chien', 'description' => '', 'prix' => '29.99', 'quantite' => 3, 'photo_nom' => null, 'espece' => 'chiens', 'slug_categorie' => 'alimentation', 'libelle_categorie' => 'Alimentation', 'univers' => 'animalier']);
        $html = rendu_chaine('partials/carte-produit.twig', ['produit' => $p + ['caracteristiques' => []]]);
        $this->assertMatchesRegularExpression('/class="carte-produit-categorie">\s*Alimentation/', $html);
    }

    // --- 4. Le mot de passe n'est jamais modifié (pas de trim) ---

    public function test_mot_de_passe_avec_espaces_utilisable(): void
    {
        $r = valider_inscription(['nom' => 'N', 'prenom' => 'P', 'email' => 'espace@test.fr', 'mot_de_passe' => '  secret123  ', 'adresse' => 'A']);
        $this->assertSame('  secret123  ', $r['donnees']['mot_de_passe']);
        inscrire($r['donnees']);
        $this->assertNotNull(connecter('espace@test.fr', '  secret123  '));
    }

    // --- 5. On commande exactement ce qui a été affiché, sinon on prévient ---

    public function test_commande_a_passer(): void
    {
        $this->assertSame([1 => 2, 4 => 1], commande_a_passer([1 => 2, 4 => 1], [1 => 2, 4 => 1, 9 => 3]));
        $this->assertNull(commande_a_passer([1 => 2], [1 => 3]));
        $this->assertNull(commande_a_passer([1 => 2], []));
        $this->assertNull(commande_a_passer([], [1 => 2]));
    }
}
