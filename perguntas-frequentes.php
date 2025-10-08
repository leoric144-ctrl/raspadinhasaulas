<?php
// Inicia a sessão para verificar o status de login
session_start();
$isAuth = isset($_SESSION['user_id']);

// Inclui o cabeçalho correto com base no status de autenticação
if ($isAuth) {
    require_once __DIR__ . '/headerlogado.php';
} else {
    require_once __DIR__ . '/header.php';
}

// O restante do conteúdo da página
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perguntas Frequentes - Raspa Green</title>

    <style>
        /* Estilos globais e de layout */
        :root {
            --bg-dark: #0D1117;
            --bg-light: #161B22;
            --border-color: #30363D;
            --text-primary: #c9d1d9;
            --text-secondary: #8b949e;
            --accent-blue: #58A6FF;
            --accent-green: #3FB950;
            --accent-red: #E24C4C;
            --max-layout-width: 1280px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background);
            color: var(--text-primary);
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        /* Estilos da nova estrutura */
        .main-container {
            max-width: 1400px;
            margin: 1rem auto;
            background: #1A1A1A;
            border-radius: 10px;
        }

        .content-wrapper {
            padding: 1.5rem;
        }

        /* Estilos do conteúdo interno (mantidos do seu código anterior) */
        .min-h-screen { min-height: 100vh; }
        .bg-surface { background-color: var(--bg-dark); }
        .rounded-b-xl { border-bottom-left-radius: 1rem; border-bottom-right-radius: 1rem; }
        .w-full { width: 100%; }
        .mb-4 { margin-bottom: 1rem; }
        .mx-auto { margin-left: auto; margin-right: auto; }
        .max-w-3xl { max-width: 48rem; }
        .px-4 { padding-left: 1rem; padding-right: 1rem; }
        .sm\:px-6 { padding-left: 1.5rem; padding-right: 1.5rem; }
        .md\:px-8 { padding-left: 2rem; padding-right: 2rem; }
        .py-6 { padding-top: 1.5rem; padding-bottom: 1.5rem; }
        .sm\:py-8 { padding-top: 2rem; padding-bottom: 2rem; }
        .md\:py-10 { padding-top: 2.5rem; padding-bottom: 2.5rem; }
        .text-center { text-align: center; }
        .text-lg { font-size: 1.125rem; }
        .sm\:text-xl { font-size: 1.25rem; }
        .md\:text-2xl { font-size: 1.5rem; }
        .font-extrabold { font-weight: 800; }
        .tracking-tight { letter-spacing: -0.025em; }
        .uppercase { text-transform: uppercase; }
        .mt-6 { margin-top: 1.5rem; }
        .sm\:mt-8 { margin-top: 2rem; }
        .space-y-6 > * + * { margin-top: 1.5rem; }
        .leading-relaxed { line-height: 1.75; }
        .text-sm { font-size: 0.875rem; }
        .sm\:text-base { font-size: 1rem; }
        .space-y-2 > * + * { margin-top: 0.5rem; }
        .block { display: block; }
        .font-semibold { font-weight: 600; }
        .opacity-70 { opacity: 0.7; }
        .hover\:opacity-100:hover { opacity: 1; }
        .transition { transition-property: opacity; transition-duration: 0.2s; transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); }
    </style>
</head>
<body>

<main class="main-container">
    <div class="content-wrapper">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 md:px-8 py-6 sm:py-8 md:py-10">
            <h2 class="text-center text-lg sm:text-xl md:text-2xl font-extrabold tracking-tight uppercase"> PERGUNTAS FREQUENTES – Raspa Green</h2>
            <div class="mt-6 sm:mt-8 space-y-6 leading-relaxed text-sm sm:text-base">

                <p class="space-y-2">
                    <strong class="block text-center text-sm sm:text-base font-semibold">1. Como faço para me cadastrar?</strong>
                    <span class="block opacity-70 hover:opacity-100 transition">É muito fácil! Basta clicar no botão "Registrar" no canto superior direito da página e preencher o formulário com seus dados. O processo é rápido e seguro.</span>
                </p>

                <p class="space-y-2">
                    <strong class="block text-center text-sm sm:text-base font-semibold">2. Como faço um depósito?</strong>
                    <span class="block opacity-70 hover:opacity-100 transition">Após fazer login, vá para a seção "Depósito". Escolha o valor que deseja depositar e siga as instruções para pagar via PIX. O saldo será creditado na sua conta instantaneamente.</span>
                </p>

                <p class="space-y-2">
                    <strong class="block text-center text-sm sm:text-base font-semibold">3. Qual é o valor mínimo para depósito e saque?</strong>
                    <span class="block opacity-70 hover:opacity-100 transition">O valor mínimo para depósito é de R$20,00. Para saques, o valor mínimo é de R$100,00.</span>
                </p>

                <p class="space-y-2">
                    <strong class="block text-center text-sm sm:text-base font-semibold">4. Como solicito um saque?</strong>
                    <span class="block opacity-70 hover:opacity-100 transition">Na sua conta, acesse a seção "Sacar". Verifique se você cumpriu o rollover de depósito (apostou o valor depositado pelo menos uma vez) e preencha os dados do seu PIX. O valor será transferido para sua conta.</span>
                </p>

                <p class="space-y-2">
                    <strong class="block text-center text-sm sm:text-base font-semibold">5. Posso jogar no meu celular?</strong>
                    <span class="block opacity-70 hover:opacity-100 transition">Sim! Nossa plataforma é totalmente responsiva e pode ser acessada de qualquer dispositivo móvel, seja smartphone ou tablet, sem a necessidade de baixar um aplicativo.</span>
                </p>

                <p class="space-y-2">
                    <strong class="block text-center text-sm sm:text-base font-semibold">6. É seguro jogar na Raspa Green?</strong>
                    <span class="block opacity-70 hover:opacity-100 transition">Sim, a segurança é nossa prioridade. Utilizamos criptografia de ponta para proteger seus dados pessoais e transações. Além disso, incentivamos o jogo responsável.</span>
                </p>

                <p class="text-center font-semibold">Se sua dúvida não foi respondida, entre em contato com nosso suporte!</p>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/footer.php'; ?>

</body>
</html>