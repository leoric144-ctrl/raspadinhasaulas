<?php
// perfil.php

// Inicia a sessão para verificar o login
session_start();

// Garante que apenas usuários logados possam acessar esta página.
// Se não houver uma sessão de usuário, redireciona para a página inicial.
if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit();
}

// Define o título que aparecerá na aba do navegador
$pageTitle = 'Meu Perfil - Raspa Green';

// Importa os componentes da página na ordem correta
require __DIR__ . '/headerlogado.php';
require __DIR__ . '/main-perfil.php';
require __DIR__ . '/footer.php';
?>