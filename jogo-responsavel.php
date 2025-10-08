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
    <title>Política de Jogo Responsável - Raspa Green</title>

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
            <h2 class="text-center text-lg sm:text-xl md:text-2xl font-extrabold tracking-tight uppercase"> POLÍTICA DE JOGO RESPONSÁVEL – Raspa Green</h2>
            <div class="mt-6 sm:mt-8 space-y-6 leading-relaxed text-sm sm:text-base">
                <p class="space-y-2">
                    <strong class="block text-center text-sm sm:text-base font-semibold"> 1. Ajuda e apoio ao jogador </strong>
                    <span class="block transition"> Na Raspa Green, acreditamos que jogar deve ser sempre uma forma de entretenimento e diversão. Organizações de apoio: </span>
                    <span class="block opacity-70 hover:opacity-100 transition"> - Gambling Therapy: www.gamblingtherapy.org/pt-br </span>
                    <span class="block opacity-70 hover:opacity-100 transition"> - Jogadores Anônimos Brasil: jogadoresanonimos.com.br </span>
                    <span class="block opacity-70 hover:opacity-100 transition"> - Grupo Salvando Vidas: gruposalvandovidas.com.br </span>
                    <span class="block opacity-70 hover:opacity-100 transition"> - Ministério da Saúde – CAPS </span>
                    <span class="block opacity-70 hover:opacity-100 transition"> - PRO-AMJO (HCFMUSP): (11) 2661-7805 / (11) 2307-7805 </span>
                    <span class="block opacity-70 hover:opacity-100 transition"> - Associação Viver Bem: (11) 2307-7804 </span>
                    <span class="block opacity-70 hover:opacity-100 transition"> - PROAD (UNIFESP): (11) 5579-1543 / (11) 94147-0763 </span>
                </p>
                <p class="space-y-2">
                    <strong class="block text-center text-sm sm:text-base font-semibold"> 2. Nosso compromisso </strong>
                    <span class="block opacity-70 hover:opacity-100 transition"> 🎯 Jogo é lazer, não renda. </span>
                    <span class="block opacity-70 hover:opacity-100 transition"> 🙋 Você escolhe jogar, nunca é obrigado. </span>
                    <span class="block opacity-70 hover:opacity-100 transition"> ⏳ Mais tempo não significa mais chances. </span>
                    <span class="block opacity-70 hover:opacity-100 transition"> 📚 Conhecimento não garante ganho. </span>
                    <span class="block opacity-70 hover:opacity-100 transition"> 🚫 Proibido para menores de 18 anos. </span>
                    <span class="block opacity-70 hover:opacity-100 transition"> 👩‍🏫 Treinamento interno de equipe. </span>
                    <span class="block opacity-70 hover:opacity-100 transition"> 📢 Comunicação responsável e transparente. </span>
                </p>
                <p class="space-y-2">
                    <strong class="block text-center text-sm sm:text-base font-semibold"> 3. Práticas de jogo responsável </strong>
                    <span class="block opacity-70 hover:opacity-100 transition"> - Aposte apenas valores que esteja disposto a perder. </span>
                    <span class="block opacity-70 hover:opacity-100 transition"> - Nunca jogue para recuperar perdas. </span>
                    <span class="block opacity-70 hover:opacity-100 transition"> - Leia sempre as regras antes de jogar. </span>
                    <span class="block opacity-70 hover:opacity-100 transition"> - Defina limites de tempo e dinheiro. </span>
                    <span class="block opacity-70 hover:opacity-100 transition"> - Não jogue sob efeito de álcool ou drogas. </span>
                    <span class="block opacity-70 hover:opacity-100 transition"> - Faça pausas regulares. </span>
                    <span class="block opacity-70 hover:opacity-100 transition"> - Equilibre o jogo com outras atividades de lazer. </span>
                </p>
                <p class="space-y-2">
                    <strong class="block text-center text-sm sm:text-base font-semibold"> 4. Transtorno do jogo </strong>
                    <span class="block opacity-70 hover:opacity-100 transition"> O jogo deixa de ser saudável quando compromete sua vida pessoal, profissional ou financeira. </span>
                    <span class="block opacity-70 hover:opacity-100 transition"> Caso identifique sinais de alerta, interrompa imediatamente e procure ajuda especializada. </span>
                </p>
                <p class="space-y-2">
                    <strong class="block text-center text-sm sm:text-base font-semibold"> 5. Ferramentas de controle Raspa Green</strong>
                    <span class="block opacity-70 hover:opacity-100 transition"> - Alertas de tempo. </span>
                    <span class="block opacity-70 hover:opacity-100 transition"> - Limites financeiros (diário, semanal ou mensal). </span>
                    <span class="block opacity-70 hover:opacity-100 transition"> - Pausa temporária (1 a 45 dias). </span>
                    <span class="block opacity-70 hover:opacity-100 transition"> - Autoexclusão (3 a 60 meses ou permanente). </span>
                    <span class="block opacity-70 hover:opacity-100 transition"> - Monitoramento de atividade pela equipe. </span>
                </p>
                <p class="space-y-2">
                    <strong class="block text-center text-sm sm:text-base font-semibold"> 6. Publicidade e marketing </strong>
                    <span class="block opacity-70 hover:opacity-100 transition"> 🚫 Nunca direcionada a menores. </span>
                    <span class="block opacity-70 hover:opacity-100 transition"> 🚫 Nunca como solução financeira. </span>
                    <span class="block opacity-70 hover:opacity-100 transition"> 🚫 Nunca associada a conteúdo impróprio. </span>
                    <span class="block opacity-70 hover:opacity-100 transition"> ✅ Sempre transparente e responsável. </span>
                </p>
                <p class="space-y-2">
                    <strong class="block text-center text-sm sm:text-base font-semibold"> 7. Contato </strong>
                    <span class="block opacity-70 hover:opacity-100 transition"> Em caso de dúvidas ou solicitação de ferramentas de controle, entre em contato com a equipe da Raspa Green. </span>
                    <span class="block opacity-70 hover:opacity-100 transition"> 📍 Alphaville, Barueri, SP </span>
                    <span class="block opacity-70 hover:opacity-100 transition"> 📅 Atualizada em 27 de agosto de 2025 </span>
                </p>
                <p class="text-center font-semibold">Raspa Green – Diversão com Responsabilidade. </p>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/footer.php'; ?>

</body>
</html>