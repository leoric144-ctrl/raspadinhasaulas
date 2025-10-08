<?php
// centavo-da-sorte.php

session_start();
$pageTitle = 'Centavo da Sorte - Raspa Green';

if (isset($_SESSION['user_id'])) {
    require __DIR__ . '/headerlogado.php';
} else {
    require __DIR__ . '/header.php';
}

require __DIR__ . '/main-content-sorte-instantanea.php';
require __DIR__ . '/footer.php';
?>