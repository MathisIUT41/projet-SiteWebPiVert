<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

abstract class BaseTestCase extends TestCase
{
    /** Recrée la base de test à partir des scripts SQL du projet. */
    public static function reinstallerBase(): void
    {
        $c = config()['db'];
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $cnx = new mysqli($c['host'], $c['utilisateur'], $c['mot_de_passe']);
        $cnx->set_charset('utf8mb4');
        installer_base($cnx, $c['base'], dirname(__DIR__) . '/sql');
        $cnx->close();

        db()->select_db($c['base']);
    }

    protected function setUp(): void
    {
        $_SESSION = [];
    }
}
