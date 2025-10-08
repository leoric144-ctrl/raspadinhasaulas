<?php
session_start();
if(empty($_SESSION['user_id'])){
    header('Location: /index.php');
    exit;
}

$pageTitle = 'Raspa green';
// O header é incluído antes de qualquer conteúdo principal.
require __DIR__.'/headerlogado.php';

/**
 * ✅ AJUSTE: Roteador de Conteúdo
 * Este bloco `switch` verifica o parâmetro `?view=` na URL e decide
 * qual arquivo de conteúdo principal deve ser carregado.
 */
$view = $_GET['view'] ?? 'inicio'; // Se 'view' não existir, o padrão é 'inicio'.

// Abrimos a tag <main> antes do switch para englobar o conteúdo principal.
// Isso garante que todo o conteúdo dinâmico (jogos ou destaques) esteja dentro da tag <main>.
echo '<main>';

switch ($view) {
    // --- Rotas para os Jogos ---
    case 'centavo-da-sorte':
        require __DIR__ . '/main-content-centavo-da-sorte.php';
        break;

    case 'sorte-instantanea':
        // Lembre-se de criar o arquivo: /views/main-content-sorte-instantanea.php
        require __DIR__ . '/main-content-sorte-instantanea.php';
        break;

    case 'raspadinha-suprema':
        // Lembre-se de criar o arquivo: /views/main-content-raspadinha-suprema.php
        require __DIR__ . '/main-content-raspadinha-suprema.php';
        break;

    case 'raspa-relampago':
        // Lembre-se de criar o arquivo: /views/main-content-raspadinha-suprema.php
        require __DIR__ . '/main-content-raspa-relampago.php';
        break;

    case 'raspadinha-magica':
        // Lembre-se de criar o arquivo: /views/main-content-raspadinha-suprema.php
        require __DIR__ . '/main-content-raspadinha-magica.php';
        break;

    case 'raspe-e-ganhe':
        // Lembre-se de criar o arquivo: /views/main-content-raspadinha-suprema.php
        require __DIR__ . '/main-content-raspe-e-ganhe.php';
        break;

    // --- Rota Padrão da Página Inicial ---
    case 'inicio':
    default:
        // Se a view for 'inicio' ou qualquer outra não definida,
        // carrega o conteúdo padrão da página inicial.
        require __DIR__.'/main-content.php';
        // O destaques.php será incluído aqui, DENTRO do <main>.
        require __DIR__.'/destaques.php';
        break;
}

// Fechamos a tag <main> depois do switch, para que o footer fique fora dela.
echo '</main>';

// O footer é incluído depois de todo o conteúdo principal (e, portanto, depois do <main>).
require __DIR__.'/footer.php';
?>