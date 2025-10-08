<?php // headerlogado.php ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title><?= $pageTitle ?? 'Rasoa Green'; ?></title>
<link rel="icon" type="image/x-icon" href="https://ik.imagekit.io/3kbnnws8u/raspa-green-logo.png?updatedAt=1757348357863">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>

<style>
:root{
  --primary:#28e504; --background:#111111; --foreground:#e0e0e0;
  --border:#27272a; --font-family:'Poppins',sans-serif;
}
*{padding:0;box-sizing:border-box;}
html,body{height:100%;}
body{background:var(--background);color:var(--foreground);font-family:var(--font-family);}
a{color:inherit;text-decoration:none;}
ul{list-style:none;}

.main-header{background:var(--background);}
.header-container{max-width:1400px;margin:0 auto;display:flex;align-items:center;gap:1rem;}
.header-left{display:flex;align-items:center;gap:3rem;flex:1;}
.logo-img{height:36px;object-fit:contain;}
.main-nav ul{display:flex;gap:.5rem;}
.main-nav a{font-size:.92rem;font-weight:500;color:#fafafa;padding:.4rem .8rem;border-radius:8px;transition:.3s;display: inline-flex;align-items: center;gap: 0.5rem;}
.main-nav a:hover, .main-nav a.is-active {background:#27272a;color:var(--foreground);}
.main-nav a.is-active {color:var(--primary);}
.main-nav a svg {
    width: 16px;
    height: 16px;
}

/* ====== BLOCO AUTENTICADO ====== */
.header-right-auth{
  display:flex;align-items:center;gap:.75rem;
  opacity:0;pointer-events:none;transition:opacity .2s;
}

/* Carteira + dropdown */
.wallet{position:relative; display: flex; align-items: center; gap: 0.5rem;} /* ✅ Adicionado display:flex para alinhar o pill e o refresh-btn */

.wallet-pill{
  background:#27272a;border-radius:10px;padding:.45rem .9rem .45rem .65rem;
  display:flex;align-items:center;gap:.35rem;font-weight:600;font-size:.85rem;color:#fff;
  border:0;cursor:pointer;
  flex-shrink: 0; /* Para evitar que encolha demais */
}
.wallet-pill i{font-style:normal;opacity:.85;}
.wallet-caret{transition:transform .18s;}
.wallet-pill.open .wallet-caret{transform:rotate(180deg);}

/* ✅ NOVOS ESTILOS PARA REFRESH E SPINNER */
.wallet-refresh-btn {
    background: #27272a; /* Mesma cor de fundo do wallet-pill */
    border: 0;
    border-radius: 10px; /* Borda arredondada igual ao wallet-pill */
    color: #ccc; /* Cor inicial do ícone */
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.45rem 0.5rem; /* Ajusta o padding para ser semelhante ao do wallet-pill */
    transition: background-color 0.2s, color 0.2s;
    flex-shrink: 0; /* Para evitar que encolha demais */
}
.wallet-refresh-btn:hover {
    background-color: #3a3a3a; /* Escurece um pouco no hover */
    color: var(--primary); /* Cor ao passar o mouse */
}
.wallet-refresh-btn svg {
    width: 16px;
    height: 16px;
}

/* Estilo do spinner */
.wallet-refresh-btn.spinning .refresh-icon {
    display: none; /* Oculta o ícone de refresh */
}
.wallet-refresh-btn.spinning .spinner-icon {
    display: block; /* Mostra o spinner */
    animation: spin 1s linear infinite; /* Animação de rotação */
}
.wallet-refresh-btn .spinner-icon {
    display: none; /* Oculto por padrão */
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}


.wallet-dd{
  position:absolute;right:0;top:calc(100% + 8px);
  background:#111;border:1px solid var(--border);border-radius:12px;
  box-shadow:0 10px 30px rgba(0,0,0,.55);
  width:250px;padding:14px 16px;z-index:999;
  opacity:0;visibility:hidden;transform:translateY(-6px);
  transition:opacity .18s,transform .18s;
  /* Ajuste para que o dropdown se alinhe com a wallet-pill e não com o refresh-btn */
  right: 0; /* Alinha o lado direito do dropdown com o lado direito do wallet-pill */
  left: auto; /* Garante que não haja alinhamento à esquerda */
}
/* Reajuste para o wallet-dd.show e wallet-toggle.open .wallet-caret*/
.wallet-pill.open + .wallet-refresh-btn + .wallet-caret { /* Seleção mais específica */
    transform:rotate(180deg);
}

.wallet-dd.show{opacity:1;visibility:visible;transform:translateY(0);}
.wallet-row{display:flex;justify-content:space-between;align-items:center;font-size:.9rem;margin-bottom:6px;}
.wallet-row span:first-child{color:var(--primary);font-weight:600;}
.wallet-total{display:flex;justify-content:space-between;align-items:center;font-size:1rem;font-weight:700;margin:6px 0;}
.wallet-total .total-value{color:var(--primary);}
.wallet-hint{font-size:.60rem;color:#aaa;margin:4px 0 12px;}
.wallet-withdraw{
  display:flex;align-items:center;justify-content:center;gap:6px;
  background:var(--primary);color:#000;font-weight:700;font-size:.88rem;
  padding:.7rem 1rem;border-radius:8px;text-align:center;cursor:pointer;border:0;
  width:100%;box-sizing:border-box;margin-top:12px;
}
.wallet-withdraw:hover{opacity:.9;}

/* Botões ação */
.action-btn{
  background:var(--primary);color:#000;font-weight:700;font-size:.82rem;
  padding:.45rem 1rem;border-radius:8px;display:inline-flex;align-items:center;gap:.35rem;
  transition:opacity .18s;
}
.action-btn:hover{opacity:.9;}
.action-btn--outline{
  color:#000;border:2px solid var(--primary);
}

/* USER + dropdown */
.user-area{position:relative;display:flex;align-items:center;gap:.3rem;}
.user-box{
  display:flex;align-items:center;gap:.5rem;padding:.3rem .6rem;border-radius:999px;background:#1c1c1c;
}
.user-avatar{width:32px;height:32px;border-radius:50%;object-fit:cover;}
.user-name{max-width:110px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:.85rem;color:#fff;}

.user-caret{background:none;border:0;color:#ccc;cursor:pointer;padding:.2rem;display:flex;}
.user-caret svg{transition:transform .18s;}
.user-caret.open svg{transform:rotate(180deg);}

.user-dd{
  position:absolute;right:0;top:calc(100% + 8px);
  background:#111;border:1px solid var(--border);border-radius:12px;
  box-shadow:0 10px 30px rgba(0,0,0,.55);
  min-width:260px;max-width:360px;padding:12px 12px 10px;z-index:998;
  opacity:0;visibility:hidden;transform:translateY(-6px);
  transition:opacity .18s,transform .18s;
}
.user-dd.show{opacity:1;visibility:visible;transform:translateY(0);}
.user-dd a,.user-dd button{
  width:100%;display:flex;align-items:center;gap:8px;
  padding:12px 6px;border:0;background:none;color:#fff;font-size:.9rem;
  text-align:left;border-radius:6px;cursor:pointer;
  transition:background .15s;
}
.user-dd a:hover,.user-dd button:hover{background:#222;}
.user-dd a svg,.user-dd button svg{width:22px;height:22px;flex:0 0 auto;}
.user-dd .logout{color:#ff4d6a;}
.user-dd .logout:hover{background:rgba(255,77,106,.12);}

/* ===== MOBILE (até 480px) ===== */
@media (max-width:480px){

  .main-header{
    padding:.6rem .9rem;
    position:sticky;top:0;z-index:100;
  }

  .header-container{gap:.5rem;}
  .main-nav,#btn-open-withdraw{display:none !important;}

  .header-right-auth{display:flex !important;opacity:1;pointer-events:auto;gap:.5rem;}

  .logo-img{height:30px;}

  .wallet-pill{
    padding:.35rem .55rem .35rem .45rem;
    gap:.25rem;font-size:.78rem;border-radius:8px;
  }
  .wallet-pill i{opacity:.9;font-size:.8rem;}
  .wallet-caret{width:14px;height:14px;}

  #btn-open-deposit{
    padding:.5rem;border-radius:10px;font-size:0;
    width:42px;height:42px;justify-content:center;
  }
  #btn-open-deposit svg{width:22px;height:22px;}

  .user-box{background:transparent;padding:0;gap:0;}
  .user-name{display:none;}
  .user-avatar{width:36px;height:36px;}
  .user-caret{padding:.15rem;}
  .user-caret svg{width:16px;height:16px;}

  /* dropdown carteira em tela cheia (quase) */
  .wallet{
      position:static;
      display: flex; /* Garante que o pill e o refresh-btn fiquem inline */
      gap: 0.5rem; /* Espaçamento entre eles */
  }
  .wallet-dd{
    position:fixed;
    left:12px;right:12px;
    top:64px;
    width:auto;max-height:70vh;overflow:auto;
    transform:none;border-radius:14px;z-index:9999;
  }
  .user-dd{right:12px;min-width:240px;}
}

/* ==== BOTTOM NAV (mobile only) ==================================== */
@media (max-width:992px){
  .bottom-nav{
    position:fixed;left:8px;right:8px;bottom:8px;height:61px;
    background:#111;
    border:1px solid var(--border);
    border-radius:18px;
    box-shadow:0 8px 24px rgba(0, 0, 0, 1);
    display:flex;align-items:center;gap:10px;padding:0 10px;
    z-index:1000;
  }

  .bottom-nav__btn{
    flex:1 1 0;
    display:flex;flex-direction:column;align-items:center;justify-content:center;
    gap:4px;font-size:.7rem;font-weight:500;
    color:#cfcfcf;
    background:transparent;border:0;
    transition:transform .12s,color .15s;
    -webkit-tap-highlight-color:transparent;
  }
  .bottom-nav__btn svg{width:20px;height:20px;fill:currentColor;}
  .bottom-nav__btn:active{transform:scale(.9);}
  .bottom-nav__btn.is-active{color:var(--primary);}

  .bottom-nav__fab-wrap{
    position:relative;flex:0 0 auto;transform:translateY(-22px);
  }
  .bottom-nav__fab{
    background:var(--primary);color:#ffffff;
    border:4px solid #111;
    width:58px;height:58px;border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    box-shadow:0 6px 20px rgba(40,229,4,.35);
    transition:transform .12s;
  }
  .bottom-nav__fab svg{width:26px;height:26px;fill:currentColor;}
  .bottom-nav__fab:active{transform:scale(.92);}
  .bottom-nav__fab-label{
    position:absolute;bottom:-18px;left:50%;transform:translateX(-50%);
    font-size:.7rem;font-weight:500;color:#fff;white-space:nowrap;
  }
}
@media (min-width:993px){
  .bottom-nav{display:none;}
}
</style>
</head>
<body>

<header class="main-header">
  <div class="header-container">
    <div class="header-left">
      <a href="/inicio.php" class="logo-link">
        <img src="https://ik.imagekit.io/3kbnnws8u/raspa-green-logo.png?updatedAt=1757348357863" alt="Logo" class="logo-img">
      </a>
      <nav class="main-nav">
        <ul>
          <li>
            <a href="/inicio.php">
              <svg width="1em" height="1em" viewBox="0 0 512 512" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M416 174.74V48h-80v58.45L256 32 0 272h64v208h144V320h96v160h144V272h64z"></path></svg>
              <span>Início</span>
            </a>
          </li>
          <li>
            <a href="/raspadinhas.php">
              <svg fill="currentColor" viewBox="0 0 256 256" width="1em" height="1em" xmlns="http://www.w3.org/2000/svg"><path d="M198.51 56.09C186.44 35.4 169.92 24 152 24h-48c-17.92 0-34.44 11.4-46.51 32.09C46.21 75.42 40 101 40 128s6.21 52.58 17.49 71.91C69.56 220.6 86.08 232 104 232h48c17.92 0 34.44-11.4 46.51-32.09C209.79 180.58 216 155 216 128s-6.21-52.58-17.49-71.91Zm1.28 63.91h-32a152.8 152.8 0 0 0-9.68-48h30.59c6.12 13.38 10.16 30 11.09 48Zm-20.6-64h-28.73a83 83 0 0 0-12-16H152c10 0 19.4 6 27.19 16ZM152 216h-13.51a83 83 0 0 0 12-16h28.73C171.4 210 162 216 152 216Zm36.7-32h-30.58a152.8 152.8 0 0 0 9.68-48h32c-.94 18-4.98 34.62-11.1 48Z"></path></svg>
              <span>Raspadinhas</span>
            </a>
          </li>
          <li>
            <a href="/indique.php">
              <svg viewBox="0 0 512 512" fill="currentColor" width="1em" height="1em" xmlns="http://www.w3.org/2000/svg"><path d="m190.5 68.8 34.8 59.2H152c-22.1 0-40-17.9-40-40s17.9-40 40-40h2.2c14.9 0 28.8 7.9 36.3 20.8zM64 88c0 14.4 3.5 28 9.6 40H32c-17.7 0-32 14.3-32 32v64c0 17.7 14.3 32 32 32h448c17.7 0 32-14.3 32-32v-64c0-17.7-14.3-32-32-32h-41.6c6.1-12 9.6-25.6 9.6-40 0-48.6-39.4-88-88-88h-2.2c-31.9 0-61.5 16.9-77.7 44.4L256 85.5l-24.1-41C215.7 16.9 186.1 0 154.2 0H152c-48.6 0-88 39.4-88 88zm336 0c0 22.1-17.9 40-40 40h-73.3l34.8-59.2c7.6-12.9 21.4-20.8 36.3-20.8h2.2c22.1 0 40 17.9 40 40zM32 288v176c0 26.5 21.5 48 48 48h144V288zm256 224h144c26.5 0 48-21.5 48-48V288H288z"></path></svg>
              <span>Indique e Ganhe</span>
            </a>
          </li>
        </ul>
      </nav>
    </div>

    <div class="header-right-auth" id="auth-box" style="opacity: 1; pointer-events: auto;">
      <div class="wallet">
        <button class="wallet-pill" id="wallet-toggle">
          <i>R$</i><span id="wallet-balance">0,00</span>
          <svg class="wallet-caret" viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M7 10l5 5 5-5z"></path></svg>
        </button>

        <button class="wallet-refresh-btn" id="refresh-balance-btn" aria-label="Atualizar Saldo">
            <svg class="refresh-icon" viewBox="0 0 1200 1200" fill="currentColor" width="16" height="16" xmlns="http://www.w3.org/2000/svg">
              <path d="M0,600C0,269.159,269.159,0,600,0c174.439,0,338.635,76.319,451.462,204.906V0h100V399.294H753.278v-100H999.457A500.722,500.722,0,0,0,600,100c-275.7,0-500,224.3-500,500s224.3,500,500,500,500-224.3,500-500v-.5h100v.5c0,330.841-269.159,600-600,600S0,930.841,0,600Z" data-name="União 1" fill="currentColor" id="União_1"></path>
            </svg>
            <svg class="spinner-icon" viewBox="0 0 50 50" width="16" height="16" fill="currentColor">
                <circle cx="25" cy="25" r="20" fill="none" stroke="currentColor" stroke-width="5" stroke-dasharray="80" stroke-linecap="round">
                    <animateTransform attributeName="transform" type="rotate" from="0 25 25" to="360 25 25" dur="1s" repeatCount="indefinite"></animateTransform>
                </circle>
            </svg>
        </button>

        <div class="wallet-dd" id="wallet-dd">
          <div class="wallet-row"><span>R$ <span id="saldo-real">0,00</span></span><strong>Saldo</strong></div>
          <div class="wallet-row"><span>R$ <span id="saldo-bonus">0,00</span></span><strong>Bônus</strong></div>
          <hr style="border:0;border-top:1px solid var(--border);margin:8px 0;">
          <div class="wallet-total"><span>Total</span><span class="total-value">R$ <span id="saldo-total">0,00</span></span></div>
          <p class="wallet-hint">O saldo total é a soma do seu saldo e bônus.</p>
          <button id="btn-open-withdraw-dd" class="wallet-withdraw">
            <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M22 2H2a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h3v9a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-9h3a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1ZM7 20v-2a2 2 0 0 1 2 2Zm10 0h-2a2 2 0 0 1 2-2Zm0-4a4 4 0 0 0-4 4h-2a4 4 0 0 0-4-4V8h10Zm4-6h-2V7a1 1 0 0 0-1-1H6a1 1 0 0 0-1 1v3H3V4h18Zm-9 5a3 3 0 1 0-3-3 3 3 0 0 0 3 3Zm0-4a1 1 0 1 1-1 1 1 1 0 0 1 1-1Z"></path></svg>
            Sacar
          </button>
        </div>
      </div>

      <a href="javascript:void(0)" class="action-btn" id="btn-open-deposit">
        <svg fill="none" viewBox="0 0 24 24" width="18" height="18" xmlns="http://www.w3.org/2000/svg"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 15v3m0 3v-3m0 0h-3m3 0h3"></path><path fill="currentColor" fill-rule="evenodd" d="M5 5a3 3 0 0 0-3 3v8a3 3 0 0 0 3 3h7.083A6 6 0 0 1 12 18c0-1.148.322-2.22.881-3.131A3 3 0 0 1 9 12a3 3 0 1 1 5.869.881A5.97 5.97 0 0 1 18 12c1.537 0 2.939.578 4 1.528V8a3 3 0 0 0-3-3zm7 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2z" clip-rule="evenodd"></path></svg>
        Depositar
      </a>

      <a href="javascript:void(0)" id="btn-open-withdraw" class="action-btn action-btn--outline">
        <svg viewBox="0 0 24 24" fill="currentColor" width="15" height="15"><path d="M22 2H2a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h3v9a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-9h3a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1ZM7 20v-2a2 2 0 0 1 2 2Zm10 0h-2a2 2 0 0 1 2-2Zm0-4a4 4 0 0 0-4 4h-2a4 4 0 0 0-4-4V8h10Zm4-6h-2V7a1 1 0 0 0-1-1H6a1 1 0 0 0-1 1v3H3V4h18Zm-9 5a3 3 0 1 0-3-3 3 3 0 0 0 3 3Zm0-4a1 1 0 1 1-1 1 1 1 0 0 1 1-1Z"></path></svg>
        Sacar
      </a>

      <div class="user-area" id="user-area">
        <div class="user-box">
          <img class="user-avatar" id="user-avatar" src="https://ik.imagekit.io/kyjz2djk3p/avatar-15.png?updatedAt=1757344931522" alt="">
          <span class="user-name" id="user-name">Davi Santos</span>
        </div>
        <button class="user-caret" id="user-caret" aria-label="Abrir menu">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M7 10l5 5 5-5z"></path></svg>
        </button>
        <div class="user-dd" id="user-dd" role="dialog">
          <a href="/perfil.php"><svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"></path></svg> Meu Perfil </a>
          <button onclick="openWithdrawModal()"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M22 2H2a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h3v9a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-9h3a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1ZM7 20v-2a2 2 0 0 1 2 2Zm10 0h-2a2 2 0 0 1 2-2Zm0-4a4 4 0 0 0-4 4h-2a4 4 0 0 0-4-4V8h10Zm4-6h-2V7a1 1 0 0 0-1-1H6a1 1 0 0 0-1 1v3H3V4h18Zm-9 5a3 3 0 1 0-3-3 3 3 0 0 0 3 3Zm0-4a1 1 0 1 1-1 1 1 1 0 0 1 1-1Z"></path></svg> Sacar </button>
          <a href="/perfil.php?view=historico"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="m21.986 9.74-.008-.088A5.003 5.003 0 0 0 17 5H7a4.97 4.97 0 0 0-4.987 4.737q-.014.117-.013.253v6.51c0 .925.373 1.828 1.022 2.476A3.52 3.52 0 0 0 5.5 20c1.8 0 2.504-1 3.5-3 .146-.292.992-2 3-2 1.996 0 2.853 1.707 3 2 1.004 2 1.7 3 3.5 3 .925 0 1.828-.373 2.476-1.022A3.52 3.52 0 0 0 22 16.5V10q0-.141-.014-.26zM7 12.031a2 2 0 1 1-.001-3.999A2 2 0 0 1 7 12.031zm10-5a1 1 0 1 1 0 2 1 1 0 1 1 0-2zm-2 4a1 1 0 1 1 0-2 1 1 0 1 1 0 2zm2 2a1 1 0 1 1 0-2 1 1 0 1 1 0 2zm2-2a1 1 0 1 1 0-2 1 1 0 1 1 0 2z"></path></svg> Histórico de Jogos </a>
          <a href="/perfil.php?view=transacoes"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M20 12v6a1 1 0 0 1-2 0V4a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v14c0 1.654 1.346 3 3 3h14c1.654 0 3-1.346 3-3v-6zm-6-1v2H6v-2zM6 9V7h8v2zm8 6v2h-3v-2z"></path></svg> Transações </a>
          <a href="/perfil.php?view=seguranca"><svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 0c-.69 0-1.843.265-2.928.56-1.11.3-2.229.655-2.887.87a1.54 1.54 0 0 0-1.044 1.262c-.596 4.477.787 7.795 2.465 9.99a11.8 11.8 0 0 0 2.517 2.453c.386.273.744.482 1.048.625.28.132.581.24.829.24s.548-.108.829-.24a7 7 0 0 0 1.048-.625 11.8 11.8 0 0 0 2.517-2.453c1.678-2.195 3.061-5.513 2.465-9.99a1.54 1.54 0 0 0-1.044-1.263 63 63 0 0 0-2.887-.87C9.843.266 8.69 0 8 0m0 5a1.5 1.5 0 0 1 .5 2.915l.385 1.99a.5.5 0 0 1-.491.595h-.788a.5.5 0 0 1-.49-.595l.384-1.99A1.5 1.5 0 0 1 8 5"></path></svg> Segurança </a>
          <button class="logout" onclick="location.href='/logout.php'"><svg width="16" height="16" viewBox="0 0 32 32" fill="currentColor"><path d="M6 30h12a2 2 0 0 0 2-2v-3h-2v3H6V4h12v3h2V4a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v24a2 2 0 0 0 2 2Z"></path><path d="M20.586 20.586 24.172 17H10v-2h14.172l-3.586-3.586L22 10l6 6-6 6z"></path><path d="M0 0h32v32H0z" fill="none"></path></svg> Sair </button>
        </div>
      </div>

      <div class="bottom-nav">
        <button class="bottom-nav__btn is-active" data-tab="home">
          <svg viewBox="0 0 512 512"><path d="M416 174.74V48h-80v58.45L256 32 0 272h64v208h144V320h96v160h144V272h64z"></path></svg>
          <span>Início</span>
        </button>
        <button class="bottom-nav__btn" data-tab="scratch">
          <svg class="bi bi-ticket-perforated-fill" viewBox="0 0 16 16"><path d="M0 4.5A1.5 1.5 0 0 1 1.5 3h13A1.5 1.5 0 0 1 16 4.5V6a.5.5 0 0 1-.5.5 1.5 1.5 0 0 0 0 3 .5.5 0 0 1 .5.5v1.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 11.5V10a.5.5 0 0 1 .5-.5 1.5 1.5 0 1 0 0-3A.5.5 0 0 1 0 6z"></path></svg>
          <span>Raspadinhas</span>
        </button>
        <div class="bottom-nav__fab-wrap">
          <button class="bottom-nav__fab" id="bn-deposit-fab"> <svg fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 15v3m0 3v-3m0 0h-3m3 0h3"></path><path fill="currentColor" fill-rule="evenodd" d="M5 5a3 3 0 0 0-3 3v8a3 3 0 0 0 3 3h7.083A6 6 0 0 1 12 18c0-1.148.322-2.22.881-3.131A3 3 0 0 1 9 12a3 3 0 1 1 5.869.881A5.97 5.97 0 0 1 18 12c1.537 0 2.939.578 4 1.528V8a3 3 0 0 0-3-3zm7 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2z" clip-rule="evenodd"></path></svg>
          </button>
          <span class="bottom-nav__fab-label">Depositar</span>
        </div>
        <button class="bottom-nav__btn" data-tab="indicate">
          <svg viewBox="0 0 640 512"><path d="M96 128a128 128 0 1 1 256 0 128 128 0 1 1-256 0zM0 482.3C0 383.8 79.8 304 178.3 304h91.4c98.5 0 178.3 79.8 178.3 178.3 0 16.4-13.3 29.7-29.7 29.7H29.7C13.3 512 0 498.7 0 482.3zM504 312v-64h-64c-13.3 0-24-10.7-24-24s10.7-24 24-24h64v-64c0-13.3 10.7-24 24-24s24 10.7 24 24v64h64c13.3 0 24 10.7 24 24s-10.7 24-24 24h-64v64c0 13.3-10.7 24-24 24s-24-10.7-24-24z"></path></svg>
          <span>Indique</span>
        </button>
        <button class="bottom-nav__btn" data-tab="profile">
          <svg viewBox="0 0 448 512"><path d="M224 256a128 128 0 1 0 0-256 128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3 0 498.7 13.3 512 29.7 512h388.6c16.4 0 29.7-13.3 29.7-29.7 0-98.5-79.8-178.3-178.3-178.3z"></path></svg>
          <span>Perfil</span>
        </button>
      </div>
    </div>
  </div>
</header>

<?php require __DIR__.'/deposit-modal.php'; ?>
<?php require __DIR__.'/withdraw-modal.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    const body = document.body;
    const authBox = document.getElementById('auth-box');
    const nameEl = document.getElementById('user-name');
    const avatarEl = document.getElementById('user-avatar');
    const saldoPill = document.getElementById('wallet-balance');
    const walletToggle = document.getElementById('wallet-toggle');
    const walletDD = document.getElementById('wallet-dd');
    const saldoRealEl = document.getElementById('saldo-real');
    const saldoBonusEl = document.getElementById('saldo-bonus');
    const saldoTotalEl = document.getElementById('saldo-total');
    const userCaret = document.getElementById('user-caret');
    const userDD = document.getElementById('user-dd');
    const userArea = document.getElementById('user-area');
    const refreshBalanceBtn = document.getElementById('refresh-balance-btn');

    const fetchAndUpdateBalance = async () => {
        if (refreshBalanceBtn) {
            refreshBalanceBtn.classList.add('spinning');
            refreshBalanceBtn.disabled = true;
        }
        try {
            const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
            const resp = await fetch('/api/userdetails.php', { headers: token ? { 'Authorization': 'Bearer ' + token } : {} });
            if (!resp.ok) {
                console.error('Erro ao buscar saldo:', resp.statusText);
                throw new Error('Erro ao buscar saldo');
            }
            const me = await resp.json();
            const brMoney = v => Number(v || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
            nameEl.textContent = me.name ?? me.nome ?? '';
            avatarEl.src = me.avatar || 'https://ik.imagekit.io/kyjz2djk3p/avatar-15.png?updatedAt=1757344931522';
            const saldo = Number(me.saldo ?? 0);
            const bonus = Number(me.bonus ?? 0);
            const total = saldo + bonus;
            saldoPill.textContent = brMoney(total);
            saldoRealEl.textContent = brMoney(saldo);
            saldoBonusEl.textContent = brMoney(bonus);
            saldoTotalEl.textContent = brMoney(total);
            authBox.style.opacity = 1;
            authBox.style.pointerEvents = 'auto';
        } catch (e) {
            console.error('Erro na atualização do saldo:', e);
        } finally {
            if (refreshBalanceBtn) {
                refreshBalanceBtn.classList.remove('spinning');
                refreshBalanceBtn.disabled = false;
            }
        }
    };
    await fetchAndUpdateBalance();

    // --- LÓGICA DE DROPDOWN CORRIGIDA ---
    if (walletToggle) {
        walletToggle.addEventListener('click', (e) => {
            e.stopPropagation(); // Impede a propagação do clique para o document
            walletToggle.classList.toggle('open');
            walletDD.classList.toggle('show');
            userCaret?.classList.remove('open');
            userDD?.classList.remove('show');
        });
    }

    if (refreshBalanceBtn) {
        refreshBalanceBtn.addEventListener('click', async (e) => {
            e.stopPropagation();
            await fetchAndUpdateBalance();
        });
    }

    if (userCaret) {
        userCaret.addEventListener('click', (e) => {
            e.stopPropagation(); // Impede a propagação do clique para o document
            userCaret.classList.toggle('open');
            userDD.classList.toggle('show');
            walletToggle?.classList.remove('open');
            walletDD?.classList.remove('show');
        });
    }

    document.addEventListener('click', (e) => {
        // Fecha todos os dropdowns se o clique for fora deles
        const isClickInsideWallet = walletDD.contains(e.target) || walletToggle.contains(e.target) || refreshBalanceBtn.contains(e.target);
        const isClickInsideUser = userDD.contains(e.target) || userCaret.contains(e.target);

        if (!isClickInsideWallet) {
            walletToggle?.classList.remove('open');
            walletDD?.classList.remove('show');
        }

        if (!isClickInsideUser) {
            userCaret?.classList.remove('open');
            userDD?.classList.remove('show');
        }
    });

    // --- LÓGICA DOS BOTÕES DE AÇÃO ---
    const depBtn = document.getElementById('btn-open-deposit');
    const wdBtn = document.getElementById('btn-open-withdraw');
    const wdBtnDD = document.getElementById('btn-open-withdraw-dd');
    if (depBtn && typeof openDepositModal === 'function') depBtn.addEventListener('click', e => { e.preventDefault(); openDepositModal(); });
    if (wdBtn && typeof openWithdrawModal === 'function') wdBtn.addEventListener('click', e => { e.preventDefault(); openWithdrawModal(); });
    if (wdBtnDD && typeof openWithdrawModal === 'function') wdBtnDD.addEventListener('click', e => { e.preventDefault(); openWithdrawModal(); });

    // --- LÓGICA DA BARRA DE NAVEGAÇÃO INFERIOR (Mobile) ---
    const navButtons = document.querySelectorAll('.bottom-nav__btn');
    const fabBtn = document.getElementById('bn-deposit-fab');
    navButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const tab = btn.dataset.tab;
            if (tab === 'home') window.location.href = '/inicio.php';
            if (tab === 'scratch') window.location.href = '/raspadinhas.php';
            if (tab === 'indicate') window.location.href = '/indique.php';
            if (tab === 'profile') window.location.href = '/perfil.php';
        });
    });
    if (fabBtn && typeof openDepositModal === 'function') {
        fabBtn.addEventListener('click', e => { e.preventDefault(); openDepositModal(); });
    }

    function updateActiveNavLinks() {
        const currentPath = window.location.pathname;
        const desktopLinks = document.querySelectorAll('.main-nav a');
        desktopLinks.forEach(link => {
            if (link.getAttribute('href') === currentPath) {
                link.classList.add('is-active');
            }
        });
        const mobileButtons = document.querySelectorAll('.bottom-nav__btn');
        mobileButtons.forEach(btn => btn.classList.remove('is-active'));
        const pathMap = {
            '/inicio.php': 'home',
            '/raspadinhas.php': 'scratch',
            '/indique.php': 'indicate',
            '/perfil.php': 'profile'
        };
        const activeTab = pathMap[currentPath] || (currentPath === '/' ? 'home' : null);
        if (activeTab) {
            const activeButton = document.querySelector(`.bottom-nav__btn[data-tab="${activeTab}"]`);
            if (activeButton) {
                activeButton.classList.add('is-active');
            }
        }
    }
});
</script>
<script>
(function(){
  let _polling = null;
  const INTERVAL_MS = 20000; // 20s

  async function verificarPendentes() {
    try {
      const res = await fetch('/api/financeconsult.php', { cache: 'no-store' });
      if (!res.ok) return;
      const data = await res.json();

      if (!data || !data.ok) return;

      if (data.approved > 0) {
        // TODO: atualize o saldo exibido na UI
        // Exemplo simples: emitir um evento pra quem estiver ouvindo
        document.dispatchEvent(new CustomEvent('depositosAprovados', { detail: data }));

        // Ou, se preferir, force um refresh parcial/total:
        // location.reload();

        // Ou chame uma função global que você já tenha:
        // if (window.atualizarSaldo) window.atualizarSaldo();
      }
    } catch (e) {
      console.warn('Falha ao verificar pendentes:', e);
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    // roda uma vez logo que carrega
    verificarPendentes();

    // e continua em loop
    _polling = setInterval(verificarPendentes, INTERVAL_MS);
  });
})();
</script>


</body>
</html>