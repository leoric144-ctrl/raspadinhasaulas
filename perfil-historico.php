<?php
// perfil-transacoes.php

session_start();

// Apenas usuários logados podem ver esta página.
if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php'); // Redireciona se não estiver logado
    exit();
}

$pageTitle = 'Minhas Transações - Raspa Green';

// Inclui o cabeçalho de usuário logado
require __DIR__ . '/headerlogado.php';

// Inclui o conteúdo principal da página
require __DIR__ . '/main-content-historico.php';

// Inclui o rodapé
require __DIR__ . '/footer.php';
?>