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
    <title>Política de Privacidade - Raspa Green</title>

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
            <h2 class="text-center text-lg sm:text-xl md:text-2xl font-extrabold tracking-tight uppercase"> POLÍTICA DE PRIVACIDADE – Raspa Green</h2>
            <div class="mt-6 sm:mt-8 space-y-6 leading-relaxed text-sm sm:text-base">

                <p>Nós da **Raspa Green** nos preocupamos com a sua privacidade e segurança. Esta política explica como coletamos, usamos, armazenamos e protegemos seus dados pessoais.</p>

                <p class="space-y-2">
                    <strong class="block text-center text-sm sm:text-base font-semibold">1. Dados Coletados</strong>
                    <span class="block opacity-70 hover:opacity-100 transition">Coletamos informações que você fornece ao se cadastrar, como nome completo, e-mail, telefone e senha. Também coletamos dados de transações (depósitos e saques) e informações técnicas (IP, tipo de navegador, sistema operacional) para garantir a segurança e melhorar a experiência.</span>
                </p>

                <p class="space-y-2">
                    <strong class="block text-center text-sm sm:text-base font-semibold">2. Uso dos Dados</strong>
                    <span class="block opacity-70 hover:opacity-100 transition">Utilizamos seus dados para:</span>
                    <span class="block opacity-70 hover:opacity-100 transition">• Gerenciar sua conta e processar transações.</span>
                    <span class="block opacity-70 hover:opacity-100 transition">• Oferecer suporte e comunicação.</span>
                    <span class="block opacity-70 hover:opacity-100 transition">• Melhorar a plataforma e personalizar a sua experiência.</span>
                    <span class="block opacity-70 hover:opacity-100 transition">• Prevenir fraudes e garantir a segurança.</span>
                    <span class="block opacity-70 hover:opacity-100 transition">• Cumprir obrigações legais.</span>
                </p>

                <p class="space-y-2">
                    <strong class="block text-center text-sm sm:text-base font-semibold">3. Compartilhamento de Dados</strong>
                    <span class="block opacity-70 hover:opacity-100 transition">Não vendemos nem alugamos seus dados pessoais. Podemos compartilhá-los com terceiros apenas quando necessário para a operação da plataforma (ex: processadores de pagamento) ou para cumprir a lei. Esses parceiros são obrigados a manter a confidencialidade e a segurança dos seus dados.</span>
                </p>

                <p class="space-y-2">
                    <strong class="block text-center text-sm sm:text-base font-semibold">4. Segurança</strong>
                    <span class="block opacity-70 hover:opacity-100 transition">Empregamos medidas de segurança técnicas e administrativas para proteger seus dados contra acessos não autorizados, perdas ou alterações. Isso inclui criptografia, firewalls e controle de acesso rigoroso.</span>
                </p>

                <p class="space-y-2">
                    <strong class="block text-center text-sm sm:text-base font-semibold">5. Seus Direitos</strong>
                    <span class="block opacity-70 hover:opacity-100 transition">Você tem o direito de acessar, corrigir, excluir ou solicitar a portabilidade dos seus dados. Para exercer esses direitos, entre em contato com nossa equipe de suporte.</span>
                </p>

                <p class="space-y-2">
                    <strong class="block text-center text-sm sm:text-base font-semibold">6. Alterações na Política</strong>
                    <span class="block opacity-70 hover:opacity-100 transition">Podemos atualizar esta política de tempos em tempos. Recomendamos que você a revise periodicamente. A data da última atualização estará sempre visível no final desta página.</span>
                </p>

                <p class="text-center font-semibold">Raspa Green – Diversão com Responsabilidade.</p>

                <p class="text-center font-semibold text-xs opacity-70">Última atualização: 27 de agosto de 2025</p>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/footer.php'; ?>

</body>
</html>