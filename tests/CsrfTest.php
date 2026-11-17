<?php

declare(strict_types=1);

final class CsrfTest extends BaseTestCase
{
    public function test_jeton_stable_et_verifie(): void
    {
        $j = csrf_jeton();
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $j);
        $this->assertSame($j, csrf_jeton());
        $this->assertTrue(csrf_valide($j));
        $this->assertFalse(csrf_valide('faux'));
        $this->assertFalse(csrf_valide(null));
    }
}
