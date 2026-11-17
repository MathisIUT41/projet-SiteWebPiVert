<?php

declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

if (!est_post()) {
    rediriger('index.php');
}

exiger_csrf();
deconnecter();
flash('info', 'Tu es déconnecté. À bientôt !');
rediriger('index.php');
