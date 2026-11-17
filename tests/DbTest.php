<?php

declare(strict_types=1);

final class DbTest extends BaseTestCase
{
    public function test_connexion_sur_base_de_test(): void
    {
        $this->assertSame('pivert_test', db_un('SELECT DATABASE() AS b')['b']);
    }

    public function test_requete_preparee_avec_parametre(): void
    {
        db_executer('INSERT INTO MARQUE (id_marque, nom_marque) VALUES (?, ?)', 'is', [42, 'Test']);
        $this->assertSame(42, (int) db_un('SELECT id_marque FROM MARQUE WHERE id_marque = ?', 'i', [42])['id_marque']);
        $this->assertSame([], db_tous('SELECT id_marque FROM MARQUE WHERE id_marque = ?', 'i', [999]));
    }
}
