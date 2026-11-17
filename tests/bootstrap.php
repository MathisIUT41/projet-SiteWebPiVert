<?php

declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';
require __DIR__ . '/BaseTestCase.php';

$_SESSION = [];
BaseTestCase::reinstallerBase();
