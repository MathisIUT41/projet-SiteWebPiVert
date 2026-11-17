<?php

declare(strict_types=1);

final class AuthTest extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        self::reinstallerBase();
    }

    public function test_inscription_puis_connexion(): void
    {
        $id = inscrire(['nom' => 'Dupont', 'prenom' => 'Léa', 'email' => 'lea@test.fr', 'mot_de_passe' => 'motdepasse1', 'adresse' => '1 rue A']);
        $this->assertIsInt($id);
        $this->assertNotSame('motdepasse1', db_un('SELECT mot_de_passe m FROM CLIENT WHERE id_client = ?', 'i', [$id])['m']);
        $this->assertSame('Léa', connecter('lea@test.fr', 'motdepasse1')['prenom']);
        $this->assertArrayNotHasKey('mot_de_passe', $_SESSION['client']);
        $this->assertSame('client', $_SESSION['client']['role']);
    }

    public function test_email_deja_pris(): void
    {
        $this->assertSame('email_pris', inscrire(['nom' => 'X', 'prenom' => 'Y', 'email' => 'client@pivert.demo', 'mot_de_passe' => 'motdepasse1', 'adresse' => 'Z']));
    }

    public function test_mauvais_mot_de_passe(): void
    {
        $this->assertNull(connecter('client@pivert.demo', 'faux'));
        $this->assertNull(connecter('inconnu@pivert.demo', 'demo1234'));
        $this->assertArrayNotHasKey('client', $_SESSION);
    }

    public function test_validation_inscription(): void
    {
        $r = valider_inscription(['nom' => '', 'prenom' => 'A', 'email' => 'pas-un-email', 'mot_de_passe' => 'court', 'adresse' => 'B']);
        $this->assertSame(['nom', 'email', 'mot_de_passe'], array_keys($r['erreurs']));
    }

    public function test_deconnexion_garde_le_panier(): void
    {
        connecter('client@pivert.demo', 'demo1234');
        $_SESSION['panier'] = [1 => 2];
        deconnecter();
        $this->assertNull(client_connecte());
        $this->assertSame([1 => 2], $_SESSION['panier']);
    }

    public function test_roles(): void
    {
        $this->assertTrue(peut_voir_admin(['role' => 'demo']));
        $this->assertFalse(peut_modifier_admin(['role' => 'demo']));
        $this->assertTrue(peut_modifier_admin(['role' => 'admin']));
        $this->assertFalse(peut_voir_admin(['role' => 'client']));
        $this->assertFalse(peut_voir_admin(null));
    }
}
