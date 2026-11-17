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
        db_executer('INSERT INTO ping (id) VALUES (?)', 'i', [42]);
        $this->assertSame(42, (int) db_un('SELECT id FROM ping WHERE id = ?', 'i', [42])['id']);
        $this->assertSame([], db_tous('SELECT id FROM ping WHERE id = ?', 'i', [7]));
    }
}
