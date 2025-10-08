<?php
// indique.php

session_start();

date_default_timezone_set('America/Sao_Paulo');

// Verificamos o status de login do usuário
$is_logged_in = isset($_SESSION['user_id']);

// Se o usuário ESTIVER LOGADO, mostramos a página de afiliados normal
if ($is_logged_in) {

    $pageTitle = 'Indique e Ganhe - Raspa Green';
    require __DIR__ . '/headerlogado.php';
    require __DIR__ . '/main-content-indique.php';
    require __DIR__ . '/footer.php';
    require __DIR__ . '/withdraw-modal.php';

} else {

    // Se o usuário NÃO ESTIVER LOGADO, mostramos a nova tela de "Acesse sua conta"
    $pageTitle = 'Acesse sua Conta - Raspa Gree';
    require __DIR__ . '/header.php'; // Importante: usamos o header normal que tem o modal de login
    ?>

    <style>
        .auth-required-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 4rem 1rem;
            min-height: 60vh;
            color: var(--text-primary);
        }
        .auth-required-container img {
            max-width: 383px;
            height: auto;
            margin-bottom: 2rem;
        }
        .auth-required-container h2 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
        .auth-required-container .btn-login-modal {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background-color: #28e504;
            color: #111111;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .auth-required-container .btn-login-modal:hover {
            opacity: 0.9;
        }
        .auth-required-container .back-link {
            margin-top: 1rem;
            color: #28e504;
            font-size: 0.9rem;
            text-decoration: none;
        }
        .auth-required-container .back-link:hover {
            text-decoration: underline;
        }
    </style>

    <main>
        <div class="auth-required-container">
            <img src="https://ik.imagekit.io/kyjz2djk3p/auth-CXa-BK7G.png" alt="Acesse sua conta para continuar">
            <h2>Acesse sua conta para acessar esta página</h2>

            <button class="btn-login-modal" id="open-login-modal-btn">
                <svg width="1em" height="1em" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" class="size-5 text-current"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"></path></svg>
                Entrar
            </button>

            <a href="/index.php" class="back-link">Ir para página Inicial</a>
        </div>
    </main>

    <?php
    require __DIR__ . '/footer.php';
}
?>