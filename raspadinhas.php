<?php
// raspadinhas.php

// Inicia a sessão. ESSENCIAL que seja a primeira coisa no arquivo.
session_start();

// Define o título da página
$pageTitle = 'Raspadinhas - Raspa Green';

/**
 * LÓGICA DE AUTENTICAÇÃO (MAIS CONSISTENTE)
 * * Verificamos se a variável de sessão 'user_id' foi definida durante o login.
 * Este método é mais seguro e confiável do que verificar apenas o cookie.
 */
if (isset($_SESSION['user_id'])) {
    // Se a sessão existe, o usuário está 100% LOGADO.
    require __DIR__ . '/headerlogado.php';
} else {
    // Se não há sessão, o usuário NÃO está logado.
    require __DIR__ . '/header.php';
}

/**
 * ✅ NOVO AJUSTE: Roteador de Conteúdo para raspadinhas.php
 * Este bloco `switch` verifica o parâmetro `?view=` na URL e decide
 * qual arquivo de conteúdo principal deve ser carregado.
 */
$view = $_GET['view'] ?? 'todas-raspadinhas'; // Se 'view' não existir, o padrão é 'todas-raspadinhas'.

// Abrimos a tag <main> antes do switch para englobar o conteúdo principal.
echo '<main>';

switch ($view) {
    // --- Rotas para as Páginas Individuais de Raspadinhas ---
    case 'centavo-da-sorte':
        require __DIR__ . '/main-content-centavo-da-sorte.php';
        break;
    
    case 'sorte-instantanea':
        require __DIR__ . '/main-content-sorte-instantanea.php';
        break;

    case 'raspadinha-suprema':
        require __DIR__ . '/main-content-raspadinha-suprema.php';
        break;
    
    case 'raspa-relampago':
        require __DIR__ . '/main-content-raspa-relampago.php';
        break;

    case 'raspadinha-magica':
        // Adicione aqui o arquivo correspondente para a Raspadinha Mágica
        require __DIR__ . '/main-content-raspadinha-magica.php';
        break;

    case 'raspe-e-ganhe':
        // Adicione aqui o arquivo correspondente para Raspe e Ganhe
        require __DIR__ . '/main-content-raspe-e-ganhe.php';
        break;

    // --- Rota Padrão da Página de Raspadinhas (listagem de todas) ---
    case 'todas-raspadinhas':
    default:
        // Se a view for 'todas-raspadinhas' ou qualquer outra não definida,
        // carrega o conteúdo padrão da página de listagem de raspadinhas.
        require __DIR__ . '/main-content-raspadinhas.php'; // Conteúdo que lista todas as raspadinhas
        break;
}

// Fechamos a tag <main> depois do switch, para que o footer fique fora dela.
echo '</main>';

// O footer é incluído depois de todo o conteúdo principal.
require __DIR__ . '/footer.php';
?>