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
    <title>Termos de Uso - Raspa Green</title>

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
            <h2 class="text-center text-lg sm:text-xl md:text-2xl font-extrabold tracking-tight uppercase"> TERMOS DE USO – Raspa Green</h2>
            <div class="mt-6 sm:mt-8 space-y-6 leading-relaxed text-sm sm:text-base">
                <p class="space-y-2">
                    <strong class="block text-center text-sm sm:text-base font-semibold"> 1. Idade mínima e acesso </strong>
                    <span class="block opacity-70 hover:opacity-100 transition"> 1.1. O acesso e uso da plataforma é restrito a maiores de 18 anos. </span>
                    <span class="block opacity-70 hover:opacity-100 transition"> 1.2. O cadastro realizado com dados falsos ou de terceiros é proibido e poderá resultar no bloqueio da conta. </span>
                </p>
                <p class="space-y-2">
                    <strong class="block text-center text-sm sm:text-base font-semibold"> 2. Risco de perdas </strong>
                    <span class="block opacity-70 hover:opacity-100 transition"> 2.1. A participação em jogos e raspadinhas online envolve risco financeiro real, podendo gerar ganhos ou perdas. </span>
                    <span class="block opacity-70 hover:opacity-100 transition"> 2.2. A Raspa Green não garante lucros e não se responsabiliza por eventuais prejuízos decorrentes das escolhas do usuário. </span>
                </p>
                <p class="space-y-2">
                    <strong class="block text-center text-sm sm:text-base font-semibold"> 3. Jogo consciente </strong>
                    <span class="block opacity-70 hover:opacity-100 transition"> 3.1. O usuário declara ciência de que deve jogar de forma responsável e moderada. </span>
                    <span class="block opacity-70 hover:opacity-100 transition"> 3.2. Caso identifique sinais de compulsão ou vício, recomenda-se interromper imediatamente o uso e procurar ajuda especializada. </span>
                </p>
                <p class="space-y-2">
                    <strong class="block text-center text-sm sm:text-base font-semibold"> 4. Rollover obrigatório </strong>
                    <span class="block opacity-70 hover:opacity-100 transition"> 4.1. Todos os valores depositados estão sujeitos a rollover, ou seja, o usuário deve apostar pelo menos o valor integral depositado antes de solicitar qualquer saque. </span>
                    <span class="block opacity-70 hover:opacity-100 transition"> 4.2. Bônus, promoções e créditos extras oferecidos pela plataforma poderão exigir rollover adicional, que será informado de forma clara nas regras de cada promoção. </span>
                    <span class="block opacity-70 hover:opacity-100 transition"> 4.3. O não cumprimento do rollover, seja de depósito ou de bônus, implicará na impossibilidade de realizar saques até que a exigência seja cumprida. </span>
                </p>
                <p class="space-y-2">
                    <strong class="block text-center text-sm sm:text-base font-semibold"> 5. Saques e estornos </strong>
                    <span class="block opacity-70 hover:opacity-100 transition"> 5.1. O saque somente poderá ser solicitado após: </span>
                    <span class="block opacity-70 hover:opacity-100 transition"> • Cumprimento do rollover de depósito e, quando aplicável, de bônus. </span>
                    <span class="block opacity-70 hover:opacity-100 transition"> • Solicitação de valor mínimo de R$100,00. </span>
                    <span class="block opacity-70 hover:opacity-100 transition"> • Validação da identidade do titular da conta. </span>
                    <span class="block opacity-70 hover:opacity-100 transition"> 5.2. Estornos só serão possíveis em caso de erro técnico ou duplicidade de depósito, mediante análise da plataforma, com prazo de devolução em até 7 dias úteis. </span>
                </p>
                <p class="space-y-2">
                    <strong class="block text-center text-sm sm:text-base font-semibold"> 6. Responsabilidade do usuário </strong>
                    <span class="block opacity-70 hover:opacity-100 transition"> 6.1. O usuário declara que compreende integralmente este Termo de Responsabilidade e assume total responsabilidade sobre suas escolhas de jogo. </span>
                    <span class="block opacity-70 hover:opacity-100 transition"> 6.2. A Raspa Green poderá bloquear ou encerrar contas em caso de descumprimento destes termos, fraude ou comportamento suspeito. </span>
                </p>
                <p class="text-center font-semibold">Raspa Green – Diversão com Responsabilidade. </p>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/footer.php'; ?>

</body>
</html>