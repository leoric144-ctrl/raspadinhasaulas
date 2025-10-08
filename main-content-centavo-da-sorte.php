<?php
// main-content-centavo-da-sorte.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$is_logged_in = isset($_SESSION['user_id']);
?>

<link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />

<main>
<style>
 /* --- ESTRUTURA PRINCIPAL --- */
 main { max-width: 1400px; margin: 1rem auto; background: #1A1A1A; border-radius: 10px; padding: 0.55rem; }
 /* --- VENCEDORES AO VIVO --- */
 .live-winners-container { display: flex; align-items: center; margin-bottom: 2rem; }
 .live-winners-container .live-icon-svg { flex-shrink: 0; height: 60px; width: auto; }
 .winners-swiper { overflow: hidden; margin-left: .5rem; }
 .winners-swiper .swiper-slide { display: flex !important; align-items: center; gap: .5rem; padding: .5rem 1rem; border: 1px solid #3a3a3c; border-radius: .5rem; width: auto !important; }
 .winners-swiper .winner-card-img { width: 1.75rem; height: 1.75rem; object-fit: contain; }
 .winner-info { display: flex; flex-direction: column; font-size: .7rem; color: #fff; }
 .winner-name { color: rgba(251,191,36,.75); font-weight: 500; }
 .prize-name { color: #8E8E93; }
 .prize-value { font-weight: 600; font-size: 0.8rem; }
 .prize-value-currency { color: #00E880; }
 /* --- GRID PARA O CONTEÚDO --- */
 .game-grid { display: grid; grid-template-columns: 0.9fr 1.52fr; gap: 2rem; align-items: start; }
 /* Coluna da Esquerda (Jogo) */
 .game-column-left { display: flex; flex-direction: column; gap: 1rem; }

 /* --- CARD DE RASPADINHA --- */
 .scratch-card-container { position: relative; border-radius: 8px; overflow: hidden; }
 .scratch-card-background { width: 100%; height: auto; display: block; }

 .scratch-game-area { position: absolute; inset: 0; z-index: 10; cursor: grab; }
 .prize-grid { position: absolute; inset: 0; display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; padding: 12%; box-sizing: border-box; }
 .prize-item { display: flex; align-items: center; justify-content: center; background: #333; border-radius: 5px; }
 .prize-item img { max-width: 80%; max-height: 80%; object-fit: contain; }
 .scratch-canvas { position: absolute; inset: 0; width: 100%; height: 100%; }

 .scratch-card-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(26, 26, 26, 0.85); display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.75rem; padding: 1rem; text-align: center; color: #fff; z-index: 20; transition: opacity 0.3s, visibility 0.3s; }
 .scratch-card-overlay.hidden { opacity: 0; visibility: hidden; pointer-events: none; }
 .overlay-coin-svg { width: 100px; height: 100px; margin: -0.5rem 0; }
 .overlay-price-text { font-weight: 600; font-size: 13px; }
 .overlay-buy-button { background-color: #28e504; color: #000; border: none; border-radius: 8px; height: 40px; padding: 0 10px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; }
 .overlay-buy-button:disabled { background-color: #555; color: #aaa; cursor: wait; }
 .overlay-buy-button section { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
 .overlay-buy-button .buy-text { font-weight: 600; }
 .overlay-buy-button .price-container { background-color: #0e0b0c; color: #fff; border-radius: 8px; padding: 6px; font-size: 12px; display: flex; align-items: center; gap: 4px; }
 .overlay-buy-button .price-container .currency { color: #00d492; }

 /* ✅ MUDANÇA (CSS): Estilos para os novos alertas de resultado */
 #result-alert {
    position: absolute; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(26, 26, 26, 0.85);
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    z-index: 30; text-align: center;
    opacity: 0; visibility: hidden; transition: opacity 0.3s, visibility 0.3s; pointer-events: none;
 }
 #result-alert.show { opacity: 1; visibility: visible; pointer-events: auto; }
 .result-content { display: none; flex-direction: column; align-items: center; justify-content: center; width: 100%; }
 #result-alert.show .result-content.active { display: flex; }

 /* Estilos para o alerta de DERROTA */
 .lose-result .icon { width: 80px; height: 80px; margin-bottom: 1rem; color: #fff; }
 .lose-result .text { font-size: 1.1rem; font-weight: 500; margin: 0 0 1.5rem; color: #f0f0f0; max-width: 25ch; }
 .lose-result .play-again-btn { background-color: #28e504; color: #000; border: none; border-radius: 8px; padding: 0.75rem 1.5rem; font-weight: 700; font-size: 1rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; }

 /* Estilos para o alerta de VITÓRIA */
 .win-result .prize-display { background: rgba(30,30,30,0.7); backdrop-filter: blur(10px); border-radius: 12px; padding: 1.5rem; display: flex; flex-direction: column; align-items: center;  border: 1px solid #3a3a3c;height: 191px; }
 .win-result .prize-img { width: 80px; height: 80px; object-fit: contain; }
 .win-result .prize-name { font-weight: 600; font-size: 1rem; color: #fff; }
 .win-result .prize-value { font-weight: 700; font-size: 1.25rem; color: #28e504; }
 .win-result .text { color: #00d492; font-weight: 500; margin-bottom: 1.5rem; }
 .win-result .play-again-btn { background-color: #28e504; color: #000; border: none; border-radius: 8px; padding: 0.75rem 1.5rem; font-weight: 700; font-size: 1rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; }
 .confetti { position: absolute; width: 100%; height: 100%; top: 0; left: 0; pointer-events: none; z-index: 31; }


 /* --- BOTÕES DE AÇÃO --- */
 .action-buttons-footer { display: flex; gap: 0.5rem; }
 .footer-btn { height: 40px; border: none; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-weight: 600; color: #fff; text-decoration: none; }
 .footer-btn:disabled { background-color: #555 !important; color: #aaa !important; cursor: wait; }
 .footer-btn-buy { flex: 0.9; background-color: #28e504; color: #000; }
 .footer-btn-buy section { display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; }
 .footer-btn-buy .price-container { background-color: #0e0b0c; color: #fff; border-radius: 6px; padding: 6px; font-size: 12px; display: flex; align-items: center; gap: 4px; }
 .footer-btn-buy .currency { color: #00d492; }
 .footer-btn-auto { flex: 1; background-color: #2A2A2E; gap: 0.5rem; }
 .footer-btn-icon { flex: 0.3; background-color: #2A2A2E; }
 .footer-btn svg { width: 20px; height: 20px; }
 /* --- COLUNA DIREITA --- */
 .right-column-container { display: flex; flex-direction: column; gap: 1rem; }
 .right-column-container .pix-banner-wrapper img { width: 100%; border-radius: 8px; }
 .right-column-container div[data-slot="card"] { color: #fff; padding: 1.25rem; border:1px solid #27272a; border-radius: 10px; display: flex; flex-direction: column; }
 .right-column-container div[data-slot="card"] > span { display: flex; align-items: center; gap: 0.75rem; font-size: 1.25rem; font-weight: 500; }
 .right-column-container div[data-slot="card"] svg { width: 24px; height: 24px; }
 .right-column-container .text-muted-foreground { color: #a0a0a0; font-size: 0.9rem; }
 .right-column-container kbd { background-color: #3A3A3C; padding: 2px 6px; border-radius: 4px; font-family: monospace; font-size: 0.8em; margin: 0 2px; }
 .right-column-container div[data-slot="alert"] { padding: 1.25rem; border:1px solid #27272a; border-radius: 8px; display: grid; grid-template-columns: auto 1fr; gap: 0.75rem; align-items: start; }
 .right-column-container div[data-slot="alert"] svg { color: #fff; width: 20px; height: 20px; }
 .right-column-container div[data-slot="alert-title"] { color: #28e504; font-weight: 600; }
 .right-column-container div[data-slot="alert-description"] { color: #a0a0a0; font-size: 0.9rem; line-height: 1.5; }
 /* --- Overlay de Usuário Deslogado --- */
 .logged-out-overlay { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 1rem; color: #fff; text-align: center; }
 .logged-out-overlay .icon { width: 96px; height: 96px; margin-bottom: 0.5rem; }
 .logged-out-overlay .text { font-size: 1.1rem; font-weight: 600; }
 .logged-out-overlay .register-button { background-color: #28e504; color: #111; border: none; border-radius: 8px; padding: 0.75rem 2rem; font-weight: 700; font-size: 1rem; cursor: pointer; text-decoration: none; transition: opacity 0.2s; }
 .logged-out-overlay .register-button:hover { opacity: 0.9; }
  /* CSS para a área de mensagens e animação */
.game-message-area {
    padding: 10px 15px;
    margin-top: 15px;
    border-radius: 8px;
    font-weight: 600;
    text-align: center;
    opacity: 0; /* Começa invisível */
    transition: opacity 0.4s ease-in-out, background-color 0.4s ease-in-out;
    color: #fff; /* Cor do texto padrão */
    position: absolute; /* Para posicionar sobre outros elementos se necessário */
    top: 50%; /* Exemplo de centralização */
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 1000; /* Garante que esteja acima de outros elementos */
    pointer-events: none; /* Permite cliques através dele quando invisível */
}

/* Estilo para erro de saldo insuficiente */
.game-message-area.insufficient-balance-error {
    background-color: #ff4d4f; /* Vermelho vibrante */
    opacity: 1; /* Torna visível */
    animation: shake 0.6s ease-in-out; /* Animação de tremor */
    animation-iteration-count: 1; /* Executa uma vez */
    pointer-events: auto; /* Permite interações quando visível */
}

/* Outros tipos de erro (opcional, para diferenciar) */
.game-message-area.generic-error {
    background-color: #ff4d4f; /* Amarelo para outros erros */
    opacity: 1;
}

/* Animação de tremor (keyframes) */
@keyframes shake {
    0%, 100% { transform: translate(-50%, -50%) translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translate(-50%, -50%) translateX(-8px); }
    20%, 40%, 60%, 80% { transform: translate(-50%, -50%) translateX(8px); }
}
/* Estilos para o botão "Raio" (Fast Scratch) */
#fast-scratch-button.active {
    background-color: #28a745; /* Verde quando ativo */
    color: #fff;
    border: 1px solid #28a745;
}

#fast-scratch-button:not(.active) {
    background-color: #dc3545; /* Vermelho quando desativado */
    color: #fff;
    border: 1px solid #dc3545;
}

/* Estilos para o botão "Rodada Automática" */
#auto-play-button.active {
    background-color: #28a745; /* Verde quando ativo */
    color: #fff;
    border: 1px solid #28a745;
}

#auto-play-button:not(.active) {
    background-color: #dc3545; /* Vermelho quando desativado */
    color: #fff;
    border: 1px solid #dc3545;
}

/* Estilos para o Modal de Rodadas Automáticas */
#autoplay-modal {
    display: none; /* Controlado por JS */
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8); /* Fundo semi-transparente escuro */
    z-index: 2000;
    display: flex; /* Para centralizar o conteúdo */
    justify-content: center;
    align-items: center;
    backdrop-filter: blur(5px); /* Efeito de desfoque no fundo */
}

#autoplay-modal > div {
    background: #1a1a1a; /* Fundo escuro para o modal */
    padding: 30px;
    border-radius: 12px;
    text-align: center;
    color: #eee; /* Cor do texto claro */
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
    max-width: 400px;
    width: 90%;
    border: 1px solid #333;
}

#autoplay-modal h3 {
    margin-top: 0;
    color: #f59e0b; /* Cor de destaque */
    font-size: 1.5rem;
    margin-bottom: 20px;
}

#autoplay-modal .autoplay-option-btn {
    background-color: #333;
    color: #fff;
    border: 1px solid #555;
    padding: 10px 15px;
    margin: 5px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1rem;
    transition: background-color 0.2s, border-color 0.2s;
}

#autoplay-modal .autoplay-option-btn:hover {
    background-color: #555;
    border-color: #888;
}

#autoplay-modal #cancel-autoplay-btn {
    background-color: #dc3545;
    color: #fff;
    border: none;
    padding: 10px 20px;
    margin-top: 20px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1rem;
    transition: background-color 0.2s;
}

#autoplay-modal #cancel-autoplay-btn:hover {
    background-color: #c82333;
}

/* Estilos para o custo das rodadas */
#autoplay-cost {
    font-size: 1.1rem;
    font-weight: bold;
    margin-top: 15px;
    color: #2dd4bf; /* Cor do dinheiro */
}

/* Estilos para o Spinner de Rodadas Automáticas */
.autoplay-spinner-container {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin-top: 10px;
}

.autoplay-spinner {
    border: 4px solid rgba(255, 255, 255, 0.3);
    border-top: 4px solid #f59e0b; /* Cor do spinner */
    border-radius: 50%;
    width: 20px;
    height: 20px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Estilos para ocultar o conteúdo normal do botão quando o spinner está ativo */
#auto-play-button.active .autoplay-text {
    display: none;
}
#auto-play-button.active .autoplay-spinner-wrapper {
    display: flex;
}
#auto-play-button:not(.active) .autoplay-spinner-wrapper {
    display: none;
}
@media (max-width: 480px) {
    .live-winners-container { margin-bottom: 1.5rem; }
    .live-winners-container .live-icon-svg { display: none; }
    .winners-swiper { margin-left: 0; }

    /* AJUSTES CRUCIAIS PARA AS IMAGENS DA RASPADINHA E O LAYOUT DO JOGO */
    .game-grid {
        grid-template-columns: 1fr; /* Força uma única coluna para o jogo em mobile */
        gap: 1rem; /* Reduz o espaçamento */
    }
    .game-column-left {
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }
    .scratch-card-container {
        width: 100%;
        max-width: 100%;
    }

    .prize-grid {
        /* Reduz o padding para dar mais espaço às imagens */
        padding: 8%; /* Ajuste este valor. 8% é um bom ponto de partida, mas teste. */
        gap: 4px; /* Reduz o gap entre os itens */
    }

    .prize-item {
        /* Garante que os itens de prêmio tenham um tamanho mínimo/máximo razoável */
        min-height: 70px; /* Um mínimo para não sumirem */
        max-height: 100px; /* Um máximo para não ficarem gigantes */
    }

    .prize-item img {
        max-width: 80%; /* Permite que a imagem preencha mais do seu contêiner */
        max-height: 80%;
        width: auto; /* Permite que a largura se ajuste à altura para manter proporção */
        height: auto;
    }

    /* Ajustes para o footer em mobile */
    main {
        padding-bottom: 70px; /* Adiciona padding na parte inferior do main para o footer fixo */
        /* Remova o margin-bottom: 1rem; ou ajuste-o para não haver espaçamento extra */
        margin-bottom: 0;
    }
    .action-buttons-footer {
        padding: 0.75rem 1rem; /* Padding interno para os botões e espaçamento */
        gap: 0.5rem; /* Ajusta o espaçamento entre os botões */
        border-radius: 0; /* Remove border-radius nas pontas se ele ficar fixo na borda */
    }
    .footer-btn-buy, .footer-btn-auto, .footer-btn-icon {
        flex: 1; /* Faz os botões dividirem o espaço igualmente */
        font-size: 0.85rem; /* Reduz a fonte dos botões para caberem */
        height: 45px; /* Altura um pouco maior para toque fácil */
        padding: 0 0.5rem; /* Reduz padding lateral */
    }
    .footer-btn-buy section, .footer-btn-auto section {
        justify-content: center; /* Centraliza o conteúdo dos botões */
        gap: 4px; /* Espaçamento menor entre ícone e texto */
    }
    .footer-btn-buy > section > span, .footer-btn-auto > span {
        display: inline; /* Volta a exibir o texto dos botões se foi escondido em media query anterior */
        font-size: 0.8em; /* Ajusta o tamanho da fonte para o texto do botão */
    }
    .footer-btn-buy .price-container, .overlay-buy-button .price-container {
        padding: 4px; /* Reduz padding do preço nos botões */
        font-size: 0.75em; /* Reduz a fonte do preço */
    }
    .footer-btn svg {
        width: 18px; /* Reduz o tamanho dos ícones */
        height: 18px;
    }
}
</style>

  <div class="live-winners-container">
    <svg class="live-icon-svg" viewBox="0 0 59 60" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2.381 31.8854L0.250732 32.1093L5.76436 16.3468L8.04082 16.1075L13.5753 30.7088L11.4242 30.9349L10.0667 27.2976L3.71764 27.9649L2.381 31.8854ZM6.64153 19.5306L4.34418 26.114L9.461 25.5762L7.14277 19.4779C7.101 19.3283 7.05227 19.1794 6.99657 19.0313C6.94088 18.8691 6.90607 18.7328 6.89215 18.6222C6.8643 18.7372 6.82949 18.8808 6.78772 19.0532C6.74595 19.2116 6.69722 19.3707 6.64153 19.5306Z" fill="#7B869D"></path><path d="M28.5469 21.5332C28.5469 23.0732 28.2336 24.4711 27.6071 25.727C26.9945 26.9674 26.1382 27.9814 25.0382 28.769C23.9522 29.5411 22.6922 30.0026 21.2581 30.1533C19.8518 30.3011 18.5987 30.1038 17.4988 29.5614C16.4128 29.0036 15.5634 28.1688 14.9508 27.0572C14.3382 25.9456 14.0319 24.6128 14.0319 23.0588C14.0319 21.5188 14.3382 20.1286 14.9508 18.8882C15.5774 17.6464 16.4336 16.6324 17.5197 15.8462C18.6057 15.0601 19.8588 14.5924 21.2789 14.4431C22.7131 14.2924 23.9731 14.4959 25.0591 15.0538C26.1451 15.6117 26.9945 16.4464 27.6071 17.558C28.2336 18.6681 28.5469 19.9932 28.5469 21.5332ZM26.3958 21.7593C26.3958 20.5833 26.18 19.577 25.7483 18.7404C25.3306 17.9023 24.7389 17.2855 23.9731 16.8899C23.2073 16.4804 22.3093 16.3298 21.2789 16.4381C20.2625 16.5449 19.3715 16.8836 18.6057 17.4541C17.8399 18.0106 17.2412 18.7525 16.8096 19.6799C16.3919 20.6058 16.183 21.6567 16.183 22.8327C16.183 24.0087 16.3919 25.0158 16.8096 25.8539C17.2412 26.6905 17.8399 27.3136 18.6057 27.7231C19.3715 28.1326 20.2625 28.2839 21.2789 28.1771C22.3093 28.0688 23.2073 27.7294 23.9731 27.1589C24.7389 26.5745 25.3306 25.8193 25.7483 24.8934C26.18 23.966 26.3958 22.9213 26.3958 21.7593Z" fill="#7B869D"></path><path d="M5.74539 52.1851L0.200195 37.8724L3.66344 37.5084L6.46607 44.7421C6.63956 45.1801 6.79971 45.6397 6.94652 46.1208C7.09332 46.6018 7.2468 47.156 7.40695 47.7833C7.59379 47.0525 7.76061 46.4445 7.90742 45.9594C8.06757 45.4729 8.22772 44.9998 8.38787 44.5401L11.1505 36.7215L14.5336 36.3659L9.08853 51.8337L5.74539 52.1851Z" fill="#00E880"></path><path d="M19.3247 35.8623V50.7578L16.0816 51.0987V36.2032L19.3247 35.8623Z" fill="#00E880"></path><path d="M26.4195 50.0121L20.8743 35.6995L24.3375 35.3355L27.1401 42.5692C27.3136 43.0072 27.4738 43.4667 27.6206 43.9478C27.7674 44.4289 27.9209 44.9831 28.081 45.6104C28.2679 44.8795 28.4347 44.2716 28.5815 43.7864C28.7416 43.2999 28.9018 42.8268 29.0619 42.3672L31.8245 34.5486L35.2077 34.193L29.7626 49.6608L26.4195 50.0121Z" fill="#00E880"></path><path d="M49.647 40.1029C49.647 41.6193 49.3401 42.9935 48.7261 44.2255C48.1122 45.4441 47.2581 46.4397 46.1637 47.2123C45.0694 47.9714 43.8015 48.4268 42.3602 48.5782C40.9322 48.7283 39.671 48.5388 38.5766 48.0097C37.4956 47.4658 36.6482 46.6491 36.0343 45.5595C35.4337 44.4686 35.1334 43.1649 35.1334 41.6485C35.1334 40.1321 35.4404 38.7646 36.0543 37.5461C36.6682 36.314 37.5156 35.3192 38.5967 34.5614C39.691 33.7889 40.9522 33.3275 42.3802 33.1774C43.8216 33.0259 45.0827 33.2222 46.1637 33.7661C47.2581 34.2952 48.1122 35.1045 48.7261 36.1941C49.3401 37.2836 49.647 38.5866 49.647 40.1029ZM46.2238 40.4627C46.2238 39.51 46.0703 38.7142 45.7634 38.0755C45.4564 37.4234 45.016 36.9463 44.4421 36.6443C43.8816 36.3409 43.201 36.2313 42.4002 36.3155C41.5995 36.3996 40.9122 36.653 40.3383 37.0757C39.7644 37.4983 39.324 38.0679 39.017 38.7846C38.7101 39.4878 38.5566 40.3158 38.5566 41.2686C38.5566 42.2214 38.7101 43.0238 39.017 43.6759C39.324 44.3281 39.7644 44.8051 40.3383 45.1071C40.9122 45.4091 41.5995 45.5181 42.4002 45.4339C43.201 45.3497 43.8816 45.097 44.4421 44.6758C45.016 44.2398 45.4564 43.6634 45.7634 42.9467C46.0703 42.2301 46.2238 41.4021 46.2238 40.4627Z" fill="#00E880"></path><circle cx="39" cy="20" r="6" fill="#222733"></circle><g><circle cx="39" cy="20" r="3.75" fill="#00E880"></circle></g></svg>
      <div class="swiper winners-swiper">
        <div class="swiper-wrapper">
          <?php
                    $winners = [
                        ['img'=>'https://ik.imagekit.io/kyjz2djk3p/item_chinelo_havaianas_top_branco.png','alt'=>'Chinelo Havaianas branco','name'=>'Jonas Sa******','prize'=>'Chinelo Havaianas branco','value'=>'35,00'],
                        ['img'=>'https://ik.imagekit.io/kyjz2djk3p/variant_redmi_12c_128gb_cinza.png','alt'=>'Smartphone modelo C2 NK109','name'=>'Marília Co******','prize'=>'Smartphone modelo C2 NK109','value'=>'800,00'],
                        ['img'=>'https://ik.imagekit.io/kyjz2djk3p/500-REAIS.png','alt'=>'500 Reais','name'=>'Jasmin Me****','prize'=>'500 Reais','value'=>'500,00'],
                        ['img'=>'https://ik.imagekit.io/kyjz2djk3p/variant_pop_110i_branco.png','alt'=>'Moto Honda Pop 110i','name'=>'Ricardo Go***','prize'=>'Moto Honda Pop 110i','value'=>'11.500,00'],
                        ['img'=>'https://ik.imagekit.io/kyjz2djk3p/variant_jbl_boombox_3_black.png','alt'=>'Caixa de som JBL Boombox 3','name'=>'Cezar Qu******','prize'=>'Caixa de som JBL Boombox 3','value'=>'2.500,00'],
                        ['img'=>'https://ik.imagekit.io/kyjz2djk3p/100%20REAIS.png','alt'=>'100 Reais','name'=>'Roberto Pe**','prize'=>'100 Reais','value'=>'100,00'],
                        ['img'=>'https://ik.imagekit.io/kyjz2djk3p/variant_redmi_12c_128gb_cinza.png','alt'=>'Smartphone Redmi 12C','name'=>'Leo Ne***','prize'=>'Smartphone Redmi 12C','value'=>'1.400,00'],
                        ['img'=>'https://ik.imagekit.io/kyjz2djk3p/item_cabo_para_recarga_usb_c.png','alt'=>'Cabo USB tipo C para recarga','name'=>'Márcio Sa****','prize'=>'Cabo USB tipo C para recarga','value'=>'360,00'],
                        ['img'=>'https://ik.imagekit.io/kyjz2djk3p/item_fog_o_5_bocas.png','alt'=>'Fogão de 5 bocas','name'=>'Benedito Pa****','prize'=>'Fogão de 5 bocas','value'=>'4.800,00'],
                        ['img'=>'https://ik.imagekit.io/kyjz2djk3p/variant_iphone_15_pro_256_gb_tit_nio_natural.png','alt'=>'iPhone 15 pro max','name'=>'Alessandro Az*****','prize'=>'iPhone 15 pro max','value'=>'10.800,00'],
                    ];          $all_winners = array_merge($winners, $winners, $winners, $winners);
          foreach($all_winners as $w){
            echo '<div class="swiper-slide"><img src="'.htmlspecialchars($w['img']).'" class="winner-card-img" alt="'.htmlspecialchars($w['alt']).'"><div class="winner-info"><span class="winner-name">'.htmlspecialchars($w['name']).'</span><p class="prize-name">'.htmlspecialchars($w['prize']).'</p><span class="prize-value"><span class="prize-value-currency">R$ </span>'.htmlspecialchars($w['value']).'</span></div></div>';
          }
          ?>
        </div>
      </div>
  </div>

  <div class="game-grid">
      <div class="game-column-left">
          <div class="scratch-card-container">
              <img src="https://ik.imagekit.io/kyjz2djk3p/raspeaqui.jpg" alt="Fundo do jogo Raspe Aqui" class="scratch-card-background">

              <div class="scratch-game-area">
                  <div class="prize-grid"></div>
                  <canvas class="scratch-canvas"></canvas>
              </div>

              <div id="result-alert">
                  <div class="result-content lose-result">
                      <div class="icon">
                          <svg width="5em" height="5em" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="M22.002 12.002C22.002 6.478 17.524 2 12 2S2 6.478 2 12.002c0 5.523 4.477 10.001 10.001 10.001s10.002-4.478 10.002-10.001Zm-14.25-2a1.25 1.25 0 1 1 2.498 0 1.25 1.25 0 0 1-2.499 0Zm6 0a1.25 1.25 0 1 1 2.498 0 1.25 1.25 0 0 1-2.499 0ZM15.75 14h.6a.75.75 0 0 1 0 1.5h-.6c-.618 0-1.337.16-1.998.418-.669.26-1.197.588-1.472.862a.75.75 0 1 1-1.06-1.06c.475-.476 1.212-.898 1.989-1.2.784-.306 1.69-.52 2.541-.52Z"></path></svg>
                      </div>
                      <p class="text">Sua chance de ouro está aqui: raspe de novo e conquiste!</p>
                      <button class="play-again-btn">
                          <svg width="1em" height="1em" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="m21.986 9.74-.008-.088A5.003 5.003 0 0 0 17 5H7a4.97 4.97 0 0 0-4.987 4.737q-.014.117-.013.253v6.51c0 .925.373 1.828 1.022 2.476A3.52 3.52 0 0 0 5.5 20c1.8 0 2.504-1 3.5-3 .146-.292.992-2 3-2 1.996 0 2.853 1.707 3 2 1.004 2 1.7 3 3.5 3 .925 0 1.828-.373 2.476-1.022A3.52 3.52 0 0 0 22 16.5V10q0-.141-.014-.26zM7 12.031a2 2 0 1 1-.001-3.999A2 2 0 0 1 7 12.031zm10-5a1 1 0 1 1 0 2 1 1 0 1 1 0-2zm-2 4a1 1 0 1 1 0-2 1 1 0 1 1 0 2zm2 2a1 1 0 1 1 0-2 1 1 0 1 1 0 2zm2-2a1 1 0 1 1 0-2 1 1 0 1 1 0 2z"></path></svg>
                          Jogar Novamente
                      </button>
                  </div>
                   <div class="result-content win-result">
                      <div class="prize-display">
                          <img class="prize-img" src="" alt="Prêmio">
                          <span class="prize-name"></span>
                          <p class="prize-value"></p>
                      </div>
                      <p class="text">Você brilhou! Agora vai na sequência da sorte!</p>
                      <button class="play-again-btn">
                          <svg width="1em" height="1em" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="m21.986 9.74-.008-.088A5.003 5.003 0 0 0 17 5H7a4.97 4.97 0 0 0-4.987 4.737q-.014.117-.013.253v6.51c0 .925.373 1.828 1.022 2.476A3.52 3.52 0 0 0 5.5 20c1.8 0 2.504-1 3.5-3 .146-.292.992-2 3-2 1.996 0 2.853 1.707 3 2 1.004 2 1.7 3 3.5 3 .925 0 1.828-.373 2.476-1.022A3.52 3.52 0 0 0 22 16.5V10q0-.141-.014-.26zM7 12.031a2 2 0 1 1-.001-3.999A2 2 0 0 1 7 12.031zm10-5a1 1 0 1 1 0 2 1 1 0 1 1 0-2zm-2 4a1 1 0 1 1 0-2 1 1 0 1 1 0 2zm2 2a1 1 0 1 1 0-2 1 1 0 1 1 0 2zm2-2a1 1 0 1 1 0-2 1 1 0 1 1 0 2z"></path></svg>
                          Jogar Novamente
                      </button>
                  </div>
                  <canvas class="confetti"></canvas>
              </div>

              <div class="scratch-card-overlay" id="purchase-overlay">
                  <?php if ($is_logged_in): ?>
                      <svg fill="currentColor" viewBox="0 0 256 256" xmlns="http://www.w3.org/2000/svg" class="overlay-coin-svg"><path d="M198.51 56.09C186.44 35.4 169.92 24 152 24h-48c-17.92 0-34.44 11.4-46.51 32.09C46.21 75.42 40 101 40 128s6.21 52.58 17.49 71.91C69.56 220.6 86.08 232 104 232h48c17.92 0 34.44-11.4 46.51-32.09C209.79 180.58 216 155 216 128s-6.21-52.58-17.49-71.91Zm1.28 63.91h-32a152.8 152.8 0 0 0-9.68-48h30.59c6.12 13.38 10.16 30 11.09 48Zm-20.6-64h-28.73a83 83 0 0 0-12-16H152c10 0 19.4 6 27.19 16ZM152 216h-13.51a83 83 0 0 0 12-16h28.73C171.4 210 162 216 152 216Zm36.7-32h-30.58a152.8 152.8 0 0 0 9.68-48h32c-.94 18-4.98 34.62-11.1 48Z"></path></svg>
                      <p class="overlay-price-text">Comprar por R$ 0,50</p>
                      <button class="overlay-buy-button" id="buy-button-overlay">
                          <section><div class="buy-text"> Comprar </div><div class="price-container"><span class="currency">R$</span> 0,50</div></section>
                      </button>
                  <?php else: ?>
                      <div class="logged-out-overlay">
                        <svg viewBox="0 0 640 512" fill="currentColor" xmlns="http://www.w3.org/2000/svg" class="icon"><path d="M96 128a128 128 0 1 1 256 0 128 128 0 1 1-256 0zM0 482.3C0 383.8 79.8 304 178.3 304h91.4c98.5 0 178.3 79.8 178.3 178.3 0 16.4-13.3 29.7-29.7 29.7H29.7C13.3 512 0 498.7 0 482.3zM504 312v-64h-64c-13.3 0-24-10.7-24-24s10.7-24 24-24h64v-64c0-13.3 10.7-24 24-24s24 10.7 24 24v64h64c13.3 0 24 10.7 24 24s-10.7 24-24 24h-64v64c0 13.3-10.7 24-24 24s-24-10.7-24-24z"></path></svg>
                        <p class="text">Faça login pra jogar</p>
                        <a href="#" class="register-button" id="register-btn-overlay">Registrar</a>
                      </div>
                  <?php endif; ?>
              </div>
          </div>
          <div class="action-buttons-footer">
              <?php if ($is_logged_in): ?>
                  <button class="footer-btn footer-btn-buy" id="buy-button-footer">
                      <section>
                        <svg fill="currentColor" viewBox="0 0 256 256" xmlns="http://www.w3.org/2000/svg"><path d="M198.51 56.09C186.44 35.4 169.92 24 152 24h-48c-17.92 0-34.44 11.4-46.51 32.09C46.21 75.42 40 101 40 128s6.21 52.58 17.49 71.91C69.56 220.6 86.08 232 104 232h48c17.92 0 34.44-11.4 46.51-32.09C209.79 180.58 216 155 216 128s-6.21-52.58-17.49-71.91Zm1.28 63.91h-32a152.8 152.8 0 0 0-9.68-48h30.59c6.12 13.38 10.16 30 11.09 48Zm-20.6-64h-28.73a83 83 0 0 0-12-16H152c10 0 19.4 6 27.19 16ZM152 216h-13.51a83 83 0 0 0 12-16h28.73C171.4 210 162 216 152 216Zm36.7-32h-30.58a152.8 152.8 0 0 0 9.68-48h32c-.94 18-4.98 34.62-11.1 48Z"></path></svg>
                        <span>Comprar</span>
                        <div class="price-container"><span class="currency">R$</span> 0,50</div>
                      </section>
                  </button>
                  <div id="game-messages" class="game-message-area"></div>
              <?php else: ?>
                  <a href="#" class="footer-btn footer-btn-buy" id="register-btn-footer"><section><svg viewBox="0 0 640 512" fill="currentColor" width="1em" height="1em"><path d="M96 128a128 128 0 1 1 256 0 128 128 0 1 1-256 0zM0 482.3C0 383.8 79.8 304 178.3 304h91.4c98.5 0 178.3 79.8 178.3 178.3 0 16.4-13.3 29.7-29.7 29.7H29.7C13.3 512 0 498.7 0 482.3zM504 312v-64h-64c-13.3 0-24-10.7-24-24s10.7-24 24-24h64v-64c0-13.3 10.7-24 24-24s24 10.7 24 24v64h64c13.3 0 24 10.7 24 24s-10.7 24-24 24h-64v64c0 13.3-10.7 24-24 24s-24-10.7-24-24z"></path></svg><span>Registrar</span></section></a>
              <?php endif; ?>
                <button class="footer-btn footer-btn-icon" id="fast-scratch-button">
                    <svg viewBox="0 0 1024 1024" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M848 359.3H627.7L825.8 109c4.1-5.3.4-13-6.3-13H436c-2.8 0-5.5 1.5-6.9 4L170 547.5c-3.1 5.3.7 12 6.9 12h174.4l-89.4 357.6c-1.9 7.8 7.5 13.3 13.3 7.7L853.5 373c5.2-4.9 1.7-13.7-5.5-13.7z"></path></svg>
                </button>

                <button class="footer-btn footer-btn-auto" id="auto-play-button">
                    <svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path fill="none" d="M0 0h24v24H0z"></path><path d="M12 6v1.79c0 .45.54.67.85.35l2.79-2.79c.2-.2.2-.51 0-.71l-2.79-2.79a.5.5 0 0 0-.85.36V4c-4.42 0-8 3.58-8 8 0 1.04.2 2.04.57 2.95.27.67 1.13.85 1.64.34.27-.27.38-.68.23-1.04C6.15 13.56 6 12.79 6 12c0-3.31 2.69-6 6-6zm5.79 2.71c-.27.27-.38.69-.23 1.04.28.7.44 1.46.44 2.25 0 3.31-2.69 6-6 6v-1.79c0-.45-.54-.67-.85-.35l-2.79 2.79c-.2.2-.2.51 0 .71l2.79 2.79a.5.5 0 0 0 .85-.35V20c4.42 0 8-3.58 8-8 0-1.04-.2-2.04-.57-2.95-.27-.67-1.13-.85-1.64-.34z"></path></svg>
                    <span>Rodada Automática</span>
                </button>

                <div id="autoplay-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 2000; justify-content: center; align-items: center;">
                    <div style="background: #fff; padding: 20px; border-radius: 10px; text-align: center;">
                        <h3>Quantas rodadas automáticas?</h3>
                        <div style="margin-bottom: 15px;">
                            <button class="autoplay-option-btn" data-rounds="10">10 Rodadas</button>
                            <button class="autoplay-option-btn" data-rounds="20">20 Rodadas</button>
                            <button class="autoplay-option-btn" data-rounds="50">50 Rodadas</button>
                            <button class="autoplay-option-btn" data-rounds="100">100 Rodadas</button>
                        </div>
                        <button id="cancel-autoplay-btn">Cancelar</button>
                    </div>
                </div>
          </div>
      </div>
      <div class="right-column-container">
          <div class="pix-banner-wrapper">
              <img src="https://ik.imagekit.io/kyjz2djk3p/pixnahora.png" alt="Pix na Hora!">
          </div>
          <div data-slot="card">
              <span class="title-span"><svg width="1em" height="1em" fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" class="inline size-6 mr-2"><path fill="currentColor" fill-rule="evenodd" d="M1.5 6.375c0-1.036.84-1.875 1.875-1.875h17.25c1.035 0 1.875.84 1.875 1.875v3.026a.75.75 0 0 1-.375.65 2.249 2.249 0 0 0 0 3.898.75.75 0 0 1 .375.65v3.026c0 1.035-.84 1.875-1.875 1.875H3.375A1.875 1.875 0 0 1 1.5 17.625v-3.026a.75.75 0 0 1 .374-.65 2.249 2.249 0 0 0 0-3.898.75.75 0 0 1-.374-.65zm15-1.125a.75.75 0 0 1 .75.75v.75a.75.75 0 0 1-1.5 0V6a.75.75 0 0 1 .75-.75Zm.75 4.5a.75.75 0 0 0-1.5 0v.75a.75.75 0 0 0 1.5 0zm-.75 3a.75.75 0 0 1 .75.75v.75a.75.75 0 0 1-1.5 0v-.75a.75.75 0 0 1 .75-.75Zm.75 4.5a.75.75 0 0 0-1.5 0V18a.75.75 0 0 0 1.5 0zM6 12a.75.75 0 0 1 .75-.75H12a.75.75 0 0 1 0 1.5H6.75A.75.75 0 0 1 6 12Zm.75 2.25a.75.75 0 0 0 0 1.5h3a.75.75 0 0 0 0-1.5z" clip-rule="evenodd"></path></svg> Centavo da Sorte </span>
              <p class="text-muted-foreground"> Pressione <kbd>Ctrl</kbd> <kbd>R</kbd> para comprar.</p>
              <p class="text-muted-foreground"> Pressione <kbd>Ctrl</kbd> <kbd>B</kbd> para revelar.</p>
              <p class="text-muted-foreground"> Pressione <kbd>Ctrl</kbd> <kbd>B</kbd> para revelar rápido.</p>
          </div>
          <div data-slot="alert">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"></path><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"></path><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"></path><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"></path></svg>
              <div>
                  <div data-slot="alert-title">Reúna 3 imagens iguais e conquiste seu prêmio!</div>
                  <div data-slot="alert-description"> O valor correspondente será creditado automaticamente na sua conta. <br> Se preferir receber o produto físico, basta entrar em contato com o nosso suporte. </div>
              </div>
          </div>
      </div>
  </div>
  <?php
  require __DIR__ . '/premioscentavodasorte.php';
  ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    <?php if ($is_logged_in): ?>
    // --- ELEMENTOS DO JOGO ---
    const buyButtonOverlay = document.getElementById('buy-button-overlay');
    const buyButtonFooter = document.getElementById('buy-button-footer');
    const purchaseOverlay = document.getElementById('purchase-overlay');
    const prizeGridEl = document.querySelector('.prize-grid');
    const canvas = document.querySelector('.scratch-canvas');
    const ctx = canvas.getContext('2d', { willReadFrequently: true });

    const resultAlert = document.getElementById('result-alert');
    const winResultContent = resultAlert.querySelector('.win-result');
    const loseResultContent = resultAlert.querySelector('.lose-result');
    const playAgainButtons = resultAlert.querySelectorAll('.play-again-btn');
    const confettiCanvas = resultAlert.querySelector('.confetti');
    const gameMessages = document.getElementById('game-messages');

    const fastScratchButton = document.getElementById('fast-scratch-button');
    const autoPlayButton = document.getElementById('auto-play-button');
    const autoplayModal = document.getElementById('autoplay-modal');
    const autoplayOptionButtons = autoplayModal.querySelectorAll('.autoplay-option-btn');
    const cancelAutoplayButton = document.getElementById('cancel-autoplay-btn');
    const autoplayCostDisplay = document.getElementById('autoplay-cost');
    const autoplayButtonText = autoPlayButton ? autoPlayButton.querySelector('span') : null;

    const autoPlaySpinnerWrapper = document.createElement('div');
    autoPlaySpinnerWrapper.className = 'autoplay-spinner-wrapper';
    autoPlaySpinnerWrapper.style.display = 'none';
    autoPlaySpinnerWrapper.innerHTML = `
        <div class="autoplay-spinner"></div>
        <span class="autoplay-current-round"></span>
    `;
    if (autoPlayButton) {
        autoPlayButton.appendChild(autoPlaySpinnerWrapper);
    }

    let isDrawing = false;
    let gameData = null;
    let roundInProgress = false;
    let fastScratchMode = false;
    let autoPlayMode = false;
    let autoPlayRounds = 0;
    let currentAutoPlayRound = 0;
    let autoPlayIntervalId = null;
    const BET_COST = 0.50;

    // --- FUNÇÕES DO JOGO ---
    const setupCanvas = () => {
        const container = canvas.parentElement;
        canvas.width = container.clientWidth;
        canvas.height = container.clientHeight;
        ctx.fillStyle = '#7a7a7a';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.globalCompositeOperation = 'destination-out';
    };

    const resetGame = () => {
        prizeGridEl.innerHTML = '';
        resultAlert.classList.remove('show');
        winResultContent.classList.remove('active');
        loseResultContent.classList.remove('active');
        purchaseOverlay.classList.remove('hidden');

        if (!autoPlayMode) {
            if(buyButtonOverlay) buyButtonOverlay.disabled = false;
            if(buyButtonFooter) buyButtonFooter.disabled = false;
            if(fastScratchButton) fastScratchButton.disabled = false;
            if(autoPlayButton) autoPlayButton.disabled = false;
            autoPlayButton.classList.remove('active');
            if (autoplayButtonText) autoplayButtonText.textContent = 'Rodada Automática';
            if (autoPlaySpinnerWrapper) autoPlaySpinnerWrapper.style.display = 'none';
        }

        gameData = null;
        isDrawing = false;
        roundInProgress = false;
    };

    const updateUserBalance = (newBalance) => {
        const balanceElement = document.getElementById('saldo-real');
        if (balanceElement) {
            balanceElement.textContent = newBalance.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
        }
    };

    const showResult = () => {
        if (!gameData) return;

        winResultContent.classList.remove('active');
        loseResultContent.classList.remove('active');

        if (gameData.data.prize) {
            const prize = gameData.data.prize;
            // Corrigido para garantir que prize.amount é um número, usando um valor padrão de 0.
            const prizeAmount = (parseFloat(prize.amount) || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

            winResultContent.querySelector('.prize-img').src = prize.image;
            winResultContent.querySelector('.prize-name').textContent = prize.name;
            winResultContent.querySelector('.prize-value').textContent = prizeAmount;
            winResultContent.classList.add('active');

            const confettiInstance = confetti.create(confettiCanvas, { resize: true, useWorker: true });
            confettiInstance({ particleCount: 150, spread: 90, origin: { y: 0.6 }});
        } else {
            loseResultContent.classList.add('active');
        }

        resultAlert.classList.add('show');

        if (autoPlayMode) {
            currentAutoPlayRound++;
            if (autoPlayButton) {
                const remainingRounds = autoPlayRounds - currentAutoPlayRound;
                autoPlayButton.querySelector('.autoplay-current-round').textContent = `${remainingRounds} Rodadas`;
            }

            if (currentAutoPlayRound < autoPlayRounds) {
                autoPlayIntervalId = setTimeout(() => {
                    if (autoPlayMode) {
                        resetGame();
                        handlePurchase();
                    } else {
                        resetGame();
                    }
                }, 1500);
            } else {
                stopAutoPlay();
                showErrorMessage(`Rodadas automáticas concluídas! Total de ${autoPlayRounds} rodadas.`, 'generic');
            }
        }
    };

    const showErrorMessage = (message, type = 'generic') => {
        if (gameMessages) {
            gameMessages.innerHTML = '';
            gameMessages.classList.remove('insufficient-balance-error', 'generic-error');
            gameMessages.textContent = message;
            gameMessages.classList.add(`${type}-error`);
            gameMessages.style.opacity = '1';
            gameMessages.style.pointerEvents = 'auto';
            setTimeout(() => {
                gameMessages.classList.remove(`${type}-error`);
                gameMessages.textContent = '';
                gameMessages.style.opacity = '0';
                gameMessages.style.pointerEvents = 'none';
            }, 5000);
        }
    };

    const handlePurchase = async () => {
        if (roundInProgress) {
            if (!autoPlayMode) {
                showErrorMessage('Já existe uma raspadinha em jogo! Raspe-a primeiro.', 'generic');
            }
            return;
        }

        if(buyButtonOverlay) buyButtonOverlay.disabled = true;
        if(buyButtonFooter) buyButtonFooter.disabled = true;
        if(fastScratchButton) fastScratchButton.disabled = true;

        roundInProgress = true;

        if (gameMessages) {
            gameMessages.classList.remove('insufficient-balance-error', 'generic-error');
            gameMessages.textContent = '';
            gameMessages.style.opacity = '0';
            gameMessages.style.pointerEvents = 'none';
        }

        try {
            const response = await fetch('/api/buy-game.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    game_name: 'Centavo da Sorte'
                })
            });

            if (!response.ok) {
                const err = await response.json();
                if (err.code === 'INSUFFICIENT_BALANCE') {
                    showErrorMessage('Saldo Insuficiente! Por favor, recarregue.', 'insufficient-balance');
                    if (autoPlayMode) {
                        stopAutoPlay();
                        showErrorMessage('Saldo insuficiente para continuar as rodadas automáticas!', 'insufficient-balance');
                    }
                } else if (err.code === 'RATE_LIMIT') {
                    showErrorMessage('Você está apostando muito rápido! Aguarde um pouco.', 'generic');
                } else {
                    showErrorMessage(err.error || 'Erro ao comprar. Tente novamente.', 'generic');
                }

                if (!autoPlayMode) {
                    if(buyButtonOverlay) buyButtonOverlay.disabled = false;
                    if(buyButtonFooter) buyButtonFooter.disabled = false;
                    if(autoPlayButton) autoPlayButton.disabled = false;
                    if(fastScratchButton) fastScratchButton.disabled = false;
                }
                roundInProgress = false;
                return;
            }

            gameData = await response.json();

            // Atualiza o saldo do usuário com o valor recebido da API.
            updateUserBalance(gameData.newBalance);

            purchaseOverlay.classList.add('hidden');

            prizeGridEl.innerHTML = '';
            gameData.data.grid.forEach(item => {
                prizeGridEl.innerHTML += `<div class="prize-item"><img src="${item.image}" alt="${item.name}"></div>`;
            });

            setupCanvas();

            if (fastScratchMode || autoPlayMode) {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                setTimeout(() => {
                    showResult();
                }, 300);
            }
        } catch (error) {
            console.error("Erro na compra:", error);
            showErrorMessage(`Erro de conexão ou sistema: ${error.message}`, 'generic');
            if (autoPlayMode) {
                stopAutoPlay();
                showErrorMessage('Erro durante as rodadas automáticas. Processo parado.', 'generic');
            } else {
                resetGame();
            }
        }
    };

    const getPosition = (e) => {
        const rect = canvas.getBoundingClientRect();
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;
        const touch = e.touches ? e.touches[0] : e;
        return { x: (touch.clientX - rect.left) * scaleX, y: (touch.clientY - rect.top) * scaleY };
    };

    const scratch = (e) => {
        if (!isDrawing || autoPlayMode) return;
        e.preventDefault();
        const pos = getPosition(e);
        ctx.beginPath();
        const scratchRadius = fastScratchMode ? 50 : 25;
        ctx.arc(pos.x, pos.y, scratchRadius, 0, Math.PI * 2);
        ctx.fill();
    };

    const startScratching = (e) => {
        if(purchaseOverlay.classList.contains('hidden') && gameData && !autoPlayMode) {
            isDrawing = true;
            scratch(e);
        }
    };

    const stopScratching = () => {
        if (!isDrawing) return;
        isDrawing = false;

        if (fastScratchMode) {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            showResult();
            return;
        }

        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const pixels = imageData.data;
        let transparentPixels = 0;
        for (let i = 3; i < pixels.length; i += 4) { if (pixels[i] === 0) transparentPixels++; }
        const scratchedPercent = (transparentPixels / (canvas.width * canvas.height)) * 100;
        if (scratchedPercent > 50) {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            showResult();
        }
    };

    const startAutoPlay = async (rounds) => {
        if (autoPlayMode) return;
        autoPlayMode = true;
        autoPlayRounds = rounds;
        currentAutoPlayRound = 0;
        autoplayModal.style.display = 'none';

        if(buyButtonOverlay) buyButtonOverlay.disabled = true;
        if(buyButtonFooter) buyButtonFooter.disabled = true;
        if(fastScratchButton) fastScratchButton.disabled = true;

        if (autoPlayButton) {
            autoPlayButton.classList.add('active');
            if (autoplayButtonText) autoplayButtonText.textContent = '';
            if (autoPlaySpinnerWrapper) {
                autoPlaySpinnerWrapper.style.display = 'flex';
                autoPlaySpinnerWrapper.querySelector('.autoplay-current-round').textContent = `${autoPlayRounds} Rodadas`;
            }
            autoPlayButton.disabled = false;
        }

        showErrorMessage(`Iniciando ${autoPlayRounds} rodadas automáticas...`, 'generic');
        resetGame();
        await handlePurchase();
    };

    const stopAutoPlay = () => {
        autoPlayMode = false;
        autoPlayRounds = 0;
        currentAutoPlayRound = 0;
        clearTimeout(autoPlayIntervalId);
        autoPlayIntervalId = null;
        resetGame();
        showErrorMessage('Rodadas automáticas paradas.', 'generic');
    };

    // --- EVENT LISTENERS ---
    if(buyButtonOverlay) buyButtonOverlay.addEventListener('click', handlePurchase);
    if(buyButtonFooter) buyButtonFooter.disabled = false;
    if(buyButtonFooter) buyButtonFooter.addEventListener('click', handlePurchase);

    playAgainButtons.forEach(btn => btn.addEventListener('click', resetGame));

    canvas.addEventListener('mousedown', startScratching);
    canvas.addEventListener('mousemove', scratch);
    canvas.addEventListener('mouseup', stopScratching);
    canvas.addEventListener('mouseleave', stopScratching);
    canvas.addEventListener('touchstart', startScratching, { passive: false });
    canvas.addEventListener('touchmove', scratch, { passive: false });
    canvas.addEventListener('touchend', stopScratching);

    if (fastScratchButton) {
        fastScratchButton.addEventListener('click', () => {
            if (roundInProgress) {
                showErrorMessage('Conclua a rodada atual antes de mudar o modo de raspagem.', 'generic');
                return;
            }
            if (autoPlayMode) {
                showErrorMessage('Modo de raspagem rápida não pode ser ativado durante rodadas automáticas.', 'generic');
                return;
            }
            fastScratchMode = !fastScratchMode;
            fastScratchButton.classList.toggle('active', fastScratchMode);
            showErrorMessage(`Modo de raspagem rápida: ${fastScratchMode ? 'ATIVADO' : 'DESATIVADO'}`, 'generic');
        });
    }

    if (autoPlayButton) {
        autoPlayButton.addEventListener('click', () => {
            if (autoPlayMode) {
                stopAutoPlay();
            } else if (roundInProgress) {
                showErrorMessage('Conclua a rodada atual antes de iniciar rodadas automáticas.', 'generic');
            } else {
                autoplayOptionButtons.forEach(btn => {
                    const rounds = parseInt(btn.dataset.rounds);
                    const totalCost = rounds * BET_COST;
                    btn.textContent = `${rounds} Rodadas (R$ ${totalCost.toFixed(2).replace('.', ',')})`;
                });
                autoplayModal.style.display = 'flex';
            }
        });
    }

    autoplayOptionButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const rounds = parseInt(btn.dataset.rounds);
            startAutoPlay(rounds);
        });
    });

    if (cancelAutoplayButton) {
        cancelAutoplayButton.addEventListener('click', () => {
            autoplayModal.style.display = 'none';
        });
    }

    <?php endif; ?>
});
</script>
