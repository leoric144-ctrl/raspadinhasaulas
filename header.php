<?php
session_start();
$isAuth = isset($_SESSION['user']);            // ajuste ao que você já usa
$user   = $_SESSION['user'] ?? [];              // ['nome','avatar','saldo']
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title><?= $pageTitle ?? 'Raspa Green'; ?></title>
<link rel="icon" type="image/x-icon" href="https://ik.imagekit.io/3kbnnws8u/raspa-green-logo.png?updatedAt=1757348357863">


<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

<style>
/* -------------------------------------------------
    VARS / RESET
-------------------------------------------------- */
:root{
  --primary:#28e504; --background:#111111; --foreground:#e0e0e0;
  --border:#27272a; --font-family:'Poppins',sans-serif;
}
*{padding:0;box-sizing:border-box;}
html,body{height:100%;}
body{background:var(--background);color:var(--foreground);font-family:var(--font-family);}
a{color:inherit;text-decoration:none;}
ul{list-style:none;}

/* -------------------------------------------------
    HEADER
-------------------------------------------------- */
.main-header{background:var(--background);}
.header-container{max-width:1400px;margin:0 auto;display:flex;align-items:center;gap:1rem;}
.header-left{display:flex;align-items:center;gap:3rem;flex:1 1 auto;}
.logo-img{height:36px;object-fit:contain;}
.main-nav ul{display:flex;gap:.5rem;}
.main-nav a{font-size:.92rem;font-weight:500;color:#fafafa;padding:.4rem .8rem;border-radius:8px;transition:.3s;display: inline-flex;align-items: center;gap: 0.5rem;}
.main-nav a svg {
    width: 16px;
    height: 16px;
}
.main-nav a:hover{background:#27272a;color:var(--foreground);}
.header-right{display:flex;align-items:center;gap:.75rem;}

.btn{padding:.4rem 1.2rem;border-radius:6px;font-weight:700;font-size:.85rem;cursor:pointer;transition:.2s;display:inline-flex;align-items:center;gap:.5rem;}
.btn:hover{transform:scale(1.05);}
.btn-login{background:#1c1c1c;color:var(--foreground);}
.btn-login:hover{background:#2c2c2c;}
.btn-register{background:var(--primary);color:#000;}

/* Botões extras mobile */
.auth-mobile{display:none;gap:.6rem;margin-left:auto;}
.auth-mobile .btn{padding:.45rem 1rem;font-size:.8rem;border-radius:8px;}

/* -------------------------------------------------
    MODAL BASE
-------------------------------------------------- */
.modal-overlay{
  position:fixed;inset:0;background:rgba(0,0,0,.7);
  z-index:40;opacity:0;visibility:hidden;transition:opacity .3s;
}
.modal-overlay.active{opacity:1;visibility:visible;}

.modal{
  position:fixed;z-index:50;background:var(--background);
  background-image:
    radial-gradient(ellipse 80% 50% at 50% -10%, rgba(40,229,4,.15), transparent),
    radial-gradient(ellipse 80% 50% at 50% 110%, rgba(40,229,4,.1), transparent);
  border:1px solid var(--border);border-radius:.5rem;
  box-shadow:0 10px 15px -3px rgba(0,0,0,.58),0 4px 6px -2px rgba(0,0,0,.05);
  width:90%;max-width:510px;padding:2.75rem 2.5rem;
  opacity:0;visibility:hidden;transition:opacity .3s,transform .3s,bottom .38s;
}


/* Desktop: centralizado */
@media (min-width:993px){
  .modal{top:50%;left:50%;transform:translate(-50%,-50%) scale(.95);}
  .modal.active{
    opacity:1;visibility:visible;
    transform:translate(-50%,-50%) scale(1);
  }
}

/* Mobile: bottom sheet */
@media (max-width:992px){
  .modal{
    top:auto;left:0;right:0;
    bottom:-100vh;
    transform:none;
    width:100%;max-width:none;
    border-radius:20px 20px 0 0;
    padding:5.75rem 1.25rem;
    box-shadow:0 -8px 16px -6px rgba(0,0,0,.75);
  }
  .modal.active{
    bottom:0;
    opacity:1;visibility:visible;
    transform:none !important;
  }
  .modal-header:before{
    content:'';display:block;width:60px;height:5px;border-radius:999px;background:#333;margin:-.5rem auto 1rem;
  }
}

/* Conteúdo modal */
.modal-header{text-align:center;margin-bottom:1.5rem;}
.modal-header h2{display:flex;justify-content:center;align-items:center;gap:.5rem;font-size:1.5rem;font-weight:700;}
.modal-header h2 svg{color:var(--primary);width:1.25rem;height:1.25rem;}
.modal-header p{color:rgba(224,224,224,.4);margin-top:.5rem;}

.modal-form .form-group{margin-bottom:1.5rem;}
.modal-form label{display:block;font-weight:500;margin-bottom:.25rem;text-align:left;}
.input-wrapper{position:relative;}
.modal-form input{
  width:100%;height:48px;background:transparent;border:2px solid var(--border);border-radius:.5rem;
  padding-left:2.5rem;color:var(--foreground);font-size:1rem;transition:border-color .3s;
}
.modal-form input:focus{outline:none;border-color:var(--primary);}
.modal-form input::placeholder{color:#555;}
.input-wrapper svg{position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:#777;}
.password-toggle{
  position:absolute;right:.75rem;top:50%;transform:translateY(-50%);
  color:var(--primary);cursor:pointer;font-size:.875rem;
}

.modal-submit-btn{
  width:100%;background:var(--primary);color:#000;border:none;padding:.8rem;font-size:1rem;font-weight:700;
  border-radius:.375rem;cursor:pointer;text-transform:uppercase;
}

.modal-separator{display:flex;align-items:center;text-align:center;margin:1.5rem 0;color:#555;}
.modal-separator::before,.modal-separator::after{content:'';flex:1;border-bottom:1px solid var(--border);}
.modal-separator:not(:empty)::before{margin-right:.5em;}
.modal-separator:not(:empty)::after{margin-left:.5em;}

.modal-footer{text-align:center;}
.modal-footer .register-link,
.modal-footer .login-link{color:var(--primary);font-weight:600;cursor:pointer;}

.modal-close-btn{
  position:absolute;top:1rem;right:1rem;background:none;border:none;color:#777;cursor:pointer;transition:color .2s;
}
.modal-close-btn:hover{color:#fff;}

/* Feedback */
.form-error{margin-top:.6rem;color:#ff6b6b;font-size:.8rem;}
.form-success{margin-top:.6rem;color:#28e504;font-size:.8rem;}

/* Estilo para a mensagem de indicação */
.referral-message {
    font-size: 0.9rem;
    color: var(--primary);
    text-align: center;
    margin-bottom: 1rem;
    font-weight: 600;
}

/* -------------------------------------------------
    MENU MOBILE DRAWER (se quiser manter)
-------------------------------------------------- */
.menu-toggle{display:none;background:none;border:none;color:var(--foreground);cursor:pointer;z-index:1001;}
body.menu-is-open{overflow:hidden;}
.mobile-menu-container{
  position:fixed;top:0;left:0;width:100%;height:100%;
  background:rgba(0,0,0,.6);z-index:999;visibility:hidden;opacity:0;transition:visibility .3s,opacity .3s;
}
body.menu-is-open .mobile-menu-container{visibility:visible;opacity:1;}
.mobile-menu-drawer{
  position:absolute;top:0;right:0;width:85%;max-width:320px;height:100%;background:var(--background);
  box-shadow:-5px 0 20px rgba(0,0,0,.25);padding:2.5rem 1.5rem;
  transform:translateX(100%);transition:transform .35s cubic-bezier(.25,.46,.45,.94);
}
body.menu-is-open .mobile-menu-drawer{transform:translateX(0);}
.mobile-menu-drawer .main-nav{border-bottom:1px solid var(--border);padding-bottom:1.5rem;margin-bottom:1.5rem;}
.mobile-menu-drawer .main-nav ul,
.mobile-menu-drawer .header-right{display:flex;flex-direction:column;align-items:flex-start;gap:1.5rem;}
.mobile-menu-drawer .main-nav a{font-size:1.1rem;}
.mobile-menu-drawer .header-right{width:100%;}
.mobile-menu-drawer .btn{width:100%;justify-content:center;padding:.8rem 1rem;}

/* -------------------------------------------------
    RESPONSIVO GERAL
-------------------------------------------------- */
@media (max-width:992px){
  .main-nav,.header-right{display:none;}
  .auth-mobile{display:flex;}
  .menu-toggle{display:block;}
  .main-header{padding:.75rem 1.25rem;position:sticky;top:0;z-index:100;}
  .header-left{gap:1rem;}
  .logo-img{height:32px;}
}
@media (max-width:480px){
  .main-header{padding:.65rem 1rem;}
  .logo-img{height:28px;}
  .modal-header h2{font-size:1.25rem;line-height:1.3;}
  .modal-header p{font-size:.9rem;line-height:1.4;margin-top:.4rem;}
  .modal-form .form-group{margin-bottom:1.1rem;}
  .modal-form input{height:44px;font-size:.9rem;padding-left:2.25rem;}
  .password-toggle{font-size:.8rem;}
  .modal-submit-btn{font-size:.95rem;padding:.75rem 1rem;}
  .modal-footer{font-size:.85rem;margin-top:1.1rem;}
}
@media (max-height:600px){
  .modal{max-height:90vh;overflow-y:auto;}
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

<div class="modal-overlay" id="modal-overlay"></div>

<div class="modal" id="login-modal">
  <button class="modal-close-btn" id="modal-close-btn" aria-label="Fechar">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
  </button>

  <div class="modal-header">
    <h2>
      <svg viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.0713 7.80874C10.2088 7.39624 10.7913 7.39624 10.9288 7.80874L11.735 10.23C11.9125 10.7622 12.2115 11.2458 12.6083 11.6425C13.0051 12.0391 13.4889 12.3378 14.0213 12.515L16.4413 13.3212C16.8538 13.4587 16.8538 14.0412 16.4413 14.1787L14.02 14.985C13.4878 15.1625 13.0042 15.4615 12.6075 15.8583C12.2109 16.2551 11.9122 16.7389 11.735 17.2712L10.9288 19.6912C10.8991 19.7816 10.8417 19.8602 10.7647 19.916C10.6877 19.9718 10.5951 20.0018 10.5 20.0018C10.4049 20.0018 10.3123 19.9718 10.2353 19.916C10.1583 19.8602 10.1009 19.7816 10.0713 19.6912L9.26501 17.27C9.08767 16.7379 8.78886 16.2544 8.39225 15.8577C7.99564 15.4611 7.51213 15.1623 6.98001 14.985L4.55876 14.1787C4.46843 14.1491 4.38977 14.0917 4.33401 14.0147C4.27824 13.9377 4.24822 13.8451 4.24822 13.75C4.24822 13.6549 4.27824 13.5623 4.33401 13.4853C4.38977 13.4083 4.46843 13.3509 4.55876 13.3212L6.98001 12.515C7.51213 12.3377 7.99564 12.0388 8.39225 11.6422C8.78886 11.2456 9.08767 10.7621 9.26501 10.23L10.0713 7.80874Z" fill="currentColor"/></svg>
      Bem vindo de volta!
      <svg viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg" class="-scale-100"><path d="M10.0713 7.80874C10.2088 7.39624 10.7913 7.39624 10.9288 7.80874L11.735 10.23C11.9125 10.7622 12.2115 11.2458 12.6083 11.6425C13.0051 12.0391 13.4889 12.3378 14.0213 12.515L16.4413 13.3212C16.8538 13.4587 16.8538 14.0412 16.4413 14.1787L14.02 14.985C13.4878 15.1625 13.0042 15.4615 12.6075 15.8583C12.2109 16.2551 11.9122 16.7389 11.735 17.2712L10.9288 19.6912C10.8991 19.7816 10.8417 19.8602 10.7647 19.916C10.6877 19.9718 10.5951 20.0018 10.5 20.0018C10.4049 20.0018 10.3123 19.9718 10.2353 19.916C10.1583 19.8602 10.1009 19.7816 10.0713 19.6912L9.26501 17.27C9.08767 16.7379 8.78886 16.2544 8.39225 15.8577C7.99564 15.4611 7.51213 15.1623 6.98001 14.985L4.55876 14.1787C4.46843 14.1491 4.38977 14.0917 4.33401 14.0147C4.27824 13.9377 4.24822 13.8451 4.24822 13.75C4.24822 13.6549 4.27824 13.5623 4.33401 13.4853C4.38977 13.4083 4.46843 13.3509 4.55876 13.3212L6.98001 12.515C7.51213 12.3377 7.99564 12.0388 8.39225 11.6422C8.78886 11.2456 9.08767 10.7621 9.26501 10.23L10.0713 7.80874Z" fill="currentColor"/></svg>
    </h2>
    <p>Conecte-se para acompanhar seus prêmios,<br>depósitos e muito mais.</p>
  </div>

  <form class="modal-form" id="login-form">
    <div class="form-group">
      <label for="login-email">Email</label>
      <div class="input-wrapper">
        <svg width="1em" height="1em" fill="currentColor" viewBox="0 0 48 48"><path d="M4.02 13.747A6.25 6.25 0 0 1 10.25 8h27.5a6.25 6.25 0 0 1 6.236 5.828L24.002 24.35zM4 16.567V33.75A6.25 6.25 0 0 0 10.25 40h27.5A6.25 6.25 0 0 0 44 33.75V16.646L24.582 26.87a1.25 1.25 0 0 1-1.168-.002z"></path></svg>
        <input type="email" placeholder="example@site.com" id="login-email">
      </div>
    </div>
    <div class="form-group">
      <label for="login-password">Digite sua senha</label>
      <div class="input-wrapper">
        <svg width="1em" height="1em" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C9.243 2 7 4.243 7 7v3H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2h-1V7c0-2.757-2.243-5-5-5zM9 7c0-1.654 1.346-3 3-3s3 1.346 3 3v3H9zm4 10.723V20h-2v-2.277a1.993 1.993 0 0 1 .567-3.677A2 2 0 0 1 14 16a1.99 1.99 0 0 1-1 1.723z"></path></svg>
        <input type="password" placeholder="Insira sua senha..." id="login-password">
        <span class="password-toggle">Mostrar</span>
      </div>
    </div>
    <button type="submit" class="modal-submit-btn">Entrar</button>
  </form>

  <div class="modal-separator">OU</div>
  <div class="modal-footer">
    Ainda não tem uma conta? <span class="register-link">Registrar</span>
  </div>
</div>

<div class="modal" id="register-modal">
  <button class="modal-close-btn" id="register-modal-close-btn" aria-label="Fechar">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
  </button>

  <div class="modal-header">
    <h2>
      <svg viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.0713 7.80874C10.2088 7.39624 10.7913 7.39624 10.9288 7.80874L11.735 10.23C11.9125 10.7622 12.2115 11.2458 12.6083 11.6425C13.0051 12.0391 13.4889 12.3378 14.0213 12.515L16.4413 13.3212C16.8538 13.4587 16.8538 14.0412 16.4413 14.1787L14.02 14.985C13.4878 15.1625 13.0042 15.4615 12.6075 15.8583C12.2109 16.2551 11.9122 16.7389 11.735 17.2712L10.9288 19.6912C10.8991 19.7816 10.8417 19.8602 10.7647 19.916C10.6877 19.9718 10.5951 20.0018 10.5 20.0018C10.4049 20.0018 10.3123 19.9718 10.2353 19.916C10.1583 19.8602 10.1009 19.7816 10.0713 19.6912L9.26501 17.27C9.08767 16.7379 8.78886 16.2544 8.39225 15.8577C7.99564 15.4611 7.51213 15.1623 6.98001 14.985L4.55876 14.1787C4.46843 14.1491 4.38977 14.0917 4.33401 14.0147C4.27824 13.9377 4.24822 13.8451 4.24822 13.75C4.24822 13.6549 4.27824 13.5623 4.33401 13.4853C4.38977 13.4083 4.46843 13.3509 4.55876 13.3212L6.98001 12.515C7.51213 12.3377 7.99564 12.0388 8.39225 11.6422C8.78886 11.2456 9.08767 10.7621 9.26501 10.23L10.0713 7.80874Z" fill="currentColor"/></svg>
      Crie sua conta!
      <svg viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg" class="-scale-100"><path d="M10.0713 7.80874C10.2088 7.39624 10.7913 7.39624 10.9288 7.80874L11.735 10.23C11.9125 10.7622 12.2115 11.2458 12.6083 11.6425C13.0051 12.0391 13.4889 12.3378 14.0213 12.515L16.4413 13.3212C16.8538 13.4587 16.8538 14.0412 16.4413 14.1787L14.02 14.985C13.4878 15.1625 13.0042 15.4615 12.6075 15.8583C12.2109 16.2551 11.9122 16.7389 11.735 17.2712L10.9288 19.6912C10.8991 19.7816 10.8417 19.8602 10.7647 19.916C10.6877 19.9718 10.5951 20.0018 10.5 20.0018C10.4049 20.0018 10.3123 19.9718 10.2353 19.916C10.1583 19.8602 10.1009 19.7816 10.0713 19.6912L9.26501 17.27C9.08767 16.7379 8.78886 16.2544 8.39225 15.8577C7.99564 15.4611 7.51213 15.1623 6.98001 14.985L4.55876 14.1787C4.46843 14.1491 4.38977 14.0917 4.33401 14.0147C4.27824 13.9377 4.24822 13.8451 4.24822 13.75C4.24822 13.6549 4.27824 13.5623 4.33401 13.4853C4.38977 13.4083 4.46843 13.3509 4.55876 13.3212L6.98001 12.515C7.51213 12.3377 7.99564 12.0388 8.39225 11.6422C8.78886 11.2456 9.08767 10.7621 9.26501 10.23L10.0713 7.80874Z" fill="currentColor"/></svg>
    </h2>
    <p id="register-modal-subtitle">Comece a concorrer a prêmios hoje!</p> </div>

  <form class="modal-form" id="register-form">
    <input type="hidden" id="referred-by-code-input" name="referred_by_code" value="">

    <div class="form-group">
      <label>Nome Completo</label>
      <div class="input-wrapper">
        <svg width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a5 5 0 1 0 5 5 5 5 0 0 0-5-5zm0 8a3 3 0 1 1 3-3 3 3 0 0 1-3 3zm9 11v-1a7 7 0 0 0-7-7h-4a7 7 0 0 0-7 7v1h2v-1a5 5 0 0 1 5-5h4a5 5 0 0 1 5 5v1z"></path></svg>
        <input type="text" placeholder="Digite o seu nome completo" id="reg-name">
      </div>
    </div>
    <div class="form-group">
      <label>Email</label>
      <div class="input-wrapper">
        <svg width="1em" height="1em" fill="currentColor" viewBox="0 0 48 48"><path d="M4.02 13.747A6.25 6.25 0 0 1 10.25 8h27.5a6.25 6.25 0 0 1 6.236 5.828L24.002 24.35zM4 16.567V33.75A6.25 6.25 0 0 0 10.25 40h27.5A6.25 6.25 0 0 0 44 33.75V16.646L24.582 26.87a1.25 1.25 0 0 1-1.168-.002z"></path></svg>
        <input type="email" placeholder="example@site.com" id="reg-email">
      </div>
    </div>
    <div class="form-group">
      <label>Telefone</label>
      <div class="input-wrapper">
        <svg width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24"><path d="M16.57 22a2 2 0 0 0 1.43-.59l2.71-2.71a1 1 0 0 0 0-1.41l-4-4a1 1 0 0 0-1.41 0l-1.6 1.59a1 1 0 0 0-.27.91 10.12 10.12 0 0 1-6.42-6.42 1 1 0 0 0 .9-1.55l-1.58-2a1 1 0 0 0-1.42 0l-4 4a1 1 0 0 0 0 1.41l2.71 2.71A2 2 0 0 0 4 18.16a15 15 0 0 0 12.57 3.84z"></path></svg>
        <input type="tel" placeholder="(00) 0000-0000" id="reg-phone">
      </div>
    </div>
    <div class="form-group">
      <label>Escolha uma senha</label>
      <div class="input-wrapper">
        <svg width="1em" height="1em" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C9.243 2 7 4.243 7 7v3H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2h-1V7c0-2.757-2.243-5-5-5zM9 7c0-1.654 1.346-3 3-3s3 1.346 3 3v3H9zm4 10.723V20h-2v-2.277a1.993 1.993 0 0 1 .567-3.677A2 2 0 0 1 14 16a1.99 1.99 0 0 1-1 1.723z"></path></svg>
        <input type="password" placeholder="Digite uma senha forte..." id="reg-password">
        <span class="password-toggle">Mostrar</span>
      </div>
    </div>
    <button type="submit" class="modal-submit-btn">Criar</button>
  </form>

  <div class="modal-separator">OU</div>
  <div class="modal-footer">
    Já tem uma conta? <span class="login-link">Entrar</span>
  </div>
</div>

<header class="main-header">
  <div class="header-container">
    <div class="header-left">
      <a href="/" class="logo-link">
        <img src="https://ik.imagekit.io/3kbnnws8u/raspa-green-logo.png?updatedAt=1757348357863" alt="Logo do Site" class="logo-img">
      </a>
      <nav class="main-nav">
        <ul>
          <li>
            <a href="/index.php">
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

    <div class="auth-mobile">
      <a href="#" id="m-login-btn" class="btn btn-login">Entrar</a>
      <a href="#" id="m-register-btn" class="btn btn-register">
        <svg viewBox="0 0 640 512" fill="currentColor" width="1em" height="1em"><path d="M96 128a128 128 0 1 1 256 0 128 128 0 1 1-256 0zM0 482.3C0 383.8 79.8 304 178.3 304h91.4c98.5 0 178.3 79.8 178.3 178.3 0 16.4-13.3 29.7-29.7 29.7H29.7C13.3 512 0 498.7 0 482.3zM504 312v-64h-64c-13.3 0-24-10.7-24-24s10.7-24 24-24h64v-64c0-13.3 10.7-24 24-24s24 10.7 24 24v64h64c13.3 0 24 10.7 24 24s-10.7 24-24 24h-64v64c0 13.3-10.7 24-24 24s-24-10.7-24-24z"></path></svg>
        Registrar
      </a>
    </div>

    <div class="header-right">
      <a href="#" id="login-btn" class="btn btn-login">Entrar</a>
      <a href="#" id="register-btn" class="btn btn-register">
        <svg viewBox="0 0 640 512" fill="currentColor" width="1em" height="1em"><path d="M96 128a128 128 0 1 1 256 0 128 128 0 1 1-256 0zM0 482.3C0 383.8 79.8 304 178.3 304h91.4c98.5 0 178.3 79.8 178.3 178.3 0 16.4-13.3 29.7-29.7 29.7H29.7C13.3 512 0 498.7 0 482.3zM504 312v-64h-64c-13.3 0-24-10.7-24-24s10.7-24 24-24h64v-64c0-13.3 10.7-24 24-24s24 10.7 24 24v64h64c13.3 0 24 10.7 24 24s-10.7 24-24 24h-64v64c0 13.3-10.7 24-24 24s-24-10.7-24-24z"></path></svg>
        Registrar
      </a>
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
          <button class="bottom-nav__fab" id="bn-register">
            <svg viewBox="0 0 640 512"><path d="M96 128a128 128 0 1 1 256 0 128 128 0 1 1-256 0zM0 482.3C0 383.8 79.8 304 178.3 304h91.4c98.5 0 178.3 79.8 178.3 178.3 0 16.4-13.3 29.7-29.7 29.7H29.7C13.3 512 0 498.7 0 482.3zM504 312v-64h-64c-13.3 0-24-10.7-24-24s10.7-24 24-24h64v-64c0-13.3 10.7-24 24-24s24 10.7 24 24v64h64c13.3 0 24 10.7 24 24s-10.7 24-24 24h-64v64c0 13.3-10.7 24-24 24s-24-10.7-24-24z"></path></svg>
          </button>
          <span class="bottom-nav__fab-label">Registrar</span>
      </div>

      <button class="bottom-nav__btn" data-tab="prizes">
          <svg viewBox="0 0 512 512"><path d="m190.5 68.8 34.8 59.2H152c-22.1 0-40-17.9-40-40s17.9-40 40-40h2.2c14.9 0 28.8 7.9 36.3 20.8zM64 88c0 14.4 3.5 28 9.6 40H32c-17.7 0-32 14.3-32 32v64c0 17.7 14.3 32 32 32h448c17.7 0 32-14.3 32-32v-64c0-17.7-14.3-32-32-32h-41.6c6.1-12 9.6-25.6 9.6-40 0-48.6-39.4-88-88-88h-2.2c-31.9 0-61.5 16.9-77.7 44.4L256 85.5l-24.1-41C215.7 16.9 186.1 0 154.2 0H152c-48.6 0-88 39.4-88 88zm336 0c0 22.1-17.9 40-40 40h-73.3l34.8-59.2c7.6-12.9 21.4-20.8 36.3-20.8h2.2c22.1 0 40 17.9 40 40zM32 288v176c0 26.5 21.5 48 48 48h144V288zm256 224h144c26.5 0 48-21.5 48-48V288H288z"></path></svg>
          <span>Prêmios</span>
      </button>

      <button class="bottom-nav__btn" data-tab="login">
          <svg viewBox="0 0 448 512"><path d="M224 256a128 128 0 1 0 0-256 128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3 0 498.7 13.3 512 29.7 512h388.6c16.4 0 29.7-13.3 29.7-29.7 0-98.5-79.8-178.3-178.3-178.3z"></path></svg>
          <span>Entrar</span>
      </button>
    </div>
  </div>
</header>

<script>
// ==========================================================
// VARIÁVEIS GLOBAIS PARA MODAIS
// ==========================================================
let openModal;
let registerModal;
let loginModal;

// Variável global para armazenar o código de indicação da URL
let referredByCode = '';

// ==========================================================
// FUNÇÕES GLOBAIS DE FORMULÁRIO
// ==========================================================
async function postJSON(url, data) {
    // Esta função foi modificada para retornar o JSON do erro também
    const resp = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify(data)
    });
    const json = await resp.json().catch(() => null);
    if (!resp.ok) {
        // Lança o objeto de erro completo, que pode conter a propriedade 'fields'
        throw json || new Error(`Erro HTTP ${resp.status}`);
    }
    return json;
}


// AJUSTE: Função showError agora foca em erros genéricos
function showError(form, msg) {
    clearFieldErrors(form); // Limpa erros de campo específicos primeiro
    let box = form.querySelector('.form-error');
    if (!box) {
        box = document.createElement('div');
        box.className = 'form-error';
        form.appendChild(box);
    }
    box.textContent = msg;
}

function clearError(form) {
    const box = form.querySelector('.form-error');
    if (box) box.textContent = '';
    clearFieldErrors(form); // ✅ NOVO: Garante que erros de campo também sejam limpos
}

function showSuccess(form, msg) {
    let box = form.querySelector('.form-success');
    if (!box) {
        box = document.createElement('div');
        box.className = 'form-success';
        form.appendChild(box);
    }
    box.textContent = msg;
}

// ✅ NOVO: Funções para manipular erros de campos específicos
function displayFieldErrors(form, errors) {
    clearFieldErrors(form);
    for (const fieldName in errors) {
        // Constrói o ID do input baseado no nome do campo (ex: 'email' -> '#reg-email')
        const input = form.querySelector(`#reg-${fieldName}`);
        if (input) {
            const errorElement = document.createElement('div');
            errorElement.className = 'form-error field-error'; // Adiciona classe para fácil remoção
            errorElement.textContent = errors[fieldName];
            // Insere a mensagem de erro logo após o wrapper do input
            input.closest('.input-wrapper').insertAdjacentElement('afterend', errorElement);
        }
    }
}

function clearFieldErrors(form) {
    form.querySelectorAll('.field-error').forEach(el => el.remove());
}


// ==========================================================
// LÓGICA PRINCIPAL DA PÁGINA
// ==========================================================
document.addEventListener('DOMContentLoaded', () => {
    /* ========== CAPTURAR CÓDIGO DE INDICAÇÃO DA URL ========== */
    const pathSegments = window.location.pathname.split('/r/');
    if (pathSegments.length > 1 && pathSegments[1].length > 0) {
        referredByCode = pathSegments[1].split('/')[0];
    }

    /* ========== MODAIS LOGIN / REGISTER ========== */
    const modalOverlay = document.getElementById('modal-overlay');
    loginModal = document.getElementById('login-modal');
    registerModal = document.getElementById('register-modal');

    const registerModalSubtitle = document.getElementById('register-modal-subtitle');
    const referredByCodeInput = document.getElementById('referred-by-code-input');

    const anyModalOpen = () => !!document.querySelector('.modal.active');

    openModal = (m) => {
        if (!m) return;
        document.documentElement.style.overflow = 'hidden';
        modalOverlay.classList.add('active');
        m.classList.add('active');

        if (m.id === 'register-modal') {
            if (referredByCode) {
                if (registerModalSubtitle) {
                    registerModalSubtitle.innerHTML = `Você foi convidado por <strong>${referredByCode}</strong>!`;
                }
                if (referredByCodeInput) {
                    referredByCodeInput.value = referredByCode;
                }
            } else {
                if (registerModalSubtitle) {
                    registerModalSubtitle.textContent = 'Comece a concorrer a prêmios hoje!';
                }
                if (referredByCodeInput) {
                    referredByCodeInput.value = '';
                }
            }
        }
    };

    const closeModal = (m) => {
        if (!m) return;
        m.classList.remove('active');
        if (!anyModalOpen()) {
            modalOverlay.classList.remove('active');
            setTimeout(() => { document.documentElement.style.overflow = ''; }, 350);
        }
    };

    const bindOpen = (btn, modal) => {
        if (!btn || !modal) return;
        btn.addEventListener('click', e => { e.preventDefault(); openModal(modal); });
    };

    if (loginModal && registerModal) {
        const loginBtn = document.getElementById('login-btn');
        const registerBtn = document.getElementById('register-btn');
        const mLoginBtn = document.getElementById('m-login-btn');
        const mRegisterBtn = document.getElementById('m-register-btn');
        const centavoRegisterBtnOverlay = document.getElementById('register-btn-overlay');
        const centavoRegisterBtnFooter = document.getElementById('register-btn-footer');
        const indiqueLoginBtn = document.getElementById('open-login-modal-btn');
        const loginClose = document.getElementById('modal-close-btn');
        const registerClose = document.getElementById('register-modal-close-btn');
        const openRegisterFromLogin = loginModal.querySelector('.register-link');
        const openLoginFromRegister = registerModal.querySelector('.login-link');

        bindOpen(loginBtn, loginModal);
        bindOpen(registerBtn, registerModal);
        bindOpen(mLoginBtn, loginModal);
        bindOpen(mRegisterBtn, registerModal);
        bindOpen(centavoRegisterBtnOverlay, registerModal);
        bindOpen(centavoRegisterBtnFooter, registerModal);
        bindOpen(indiqueLoginBtn, loginModal);

        loginClose?.addEventListener('click', () => closeModal(loginModal));
        registerClose?.addEventListener('click', () => closeModal(registerModal));
        modalOverlay.addEventListener('click', () => { closeModal(loginModal); closeModal(registerModal); });
        openRegisterFromLogin?.addEventListener('click', () => { closeModal(loginModal); openModal(registerModal); });
        openLoginFromRegister?.addEventListener('click', () => { closeModal(registerModal); openModal(loginModal); });
    }

    /* ========== ✅ AJUSTE 3: MÁSCARA DE TELEFONE ========== */
    const phoneInput = document.getElementById('reg-phone');
    if(phoneInput) {
        phoneInput.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, ''); // Remove tudo que não for dígito
            value = value.substring(0, 11); // Limita a 11 dígitos (DDD + 9 dígitos)

            if (value.length > 10) {
                // (XX) XXXXX-XXXX
                value = value.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
            } else if (value.length > 6) {
                // (XX) XXXX-XXXX
                value = value.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
            } else if (value.length > 2) {
                // (XX) XXXX
                value = value.replace(/^(\d{2})(\d*)/, '($1) $2');
            } else if (value.length > 0) {
                // (XX
                value = value.replace(/^(\d*)/, '($1');
            }
            e.target.value = value;
        });
    }

    /* ========== BOTTOM NAV (mobile) ========== */
    const navButtons = document.querySelectorAll('.bottom-nav__btn');
    const fabBtn = document.getElementById('bn-register');

    navButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const tab = btn.dataset.tab;
            if (tab === 'login') openModal(loginModal);
            else if (tab === 'home') window.location.href = '/index.php';
            else if (tab === 'scratch') window.location.href = '/raspadinhas.php';
            else if (tab === 'prizes') window.location.href = '/indique.php';
        });
    });

    fabBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        openModal(registerModal);
    });

    /* ========== FORMULÁRIOS ========== */
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');

    if (loginForm) {
        loginForm.addEventListener('submit', async e => {
            e.preventDefault();
            clearError(loginForm);
            const email = document.getElementById('login-email')?.value.trim();
            const password = document.getElementById('login-password')?.value;
            if (!email || !password) {
                showError(loginForm, 'Preencha email e senha.');
                return;
            }
            try {
                const data = await postJSON('/api/login.php', { email, password });
                showSuccess(loginForm, 'Login realizado!');
                window.location.href = data.redirect || '/';
            } catch (err) {
                // ✅ AJUSTE 1: Lógica de erro de login
                // Se a API de login também retornar 'fields', pode ser adaptada como a de registro.
                // Por enquanto, usamos a mensagem de erro geral.
                const errorMessage = err?.error || err.message || 'Ocorreu um erro. Tente novamente.';
                showError(loginForm, errorMessage);
            }
        });
    }

    if (registerForm) {
        registerForm.addEventListener('submit', async e => {
            e.preventDefault();
            clearError(registerForm); // Limpa todos os erros (genéricos e de campo)

            const name = document.getElementById('reg-name')?.value.trim();
            const email = document.getElementById('reg-email')?.value.trim();
            const phone = document.getElementById('reg-phone')?.value.trim();
            const password = document.getElementById('reg-password')?.value;
            const referred_by_code = document.getElementById('referred-by-code-input')?.value;

            if (!name || !email || !password || !phone) {
                showError(registerForm, 'Todos os campos são obrigatórios.');
                return;
            }
            try {
                const payload = { name, email, phone, password, referred_by_code };
                const data = await postJSON('/api/register.php', payload);
                showSuccess(registerForm, 'Conta criada com sucesso!');
                setTimeout(() => {
                   window.location.href = data.redirect || '/';
                }, 1000);

            } catch (err) {
                // ✅ AJUSTE 1: LÓGICA PARA EXIBIR ERROS
                if (err && err.fields) {
                    // Se a API retornou erros de campo específicos
                    displayFieldErrors(registerForm, err.fields);
                } else {
                    // Caso contrário, mostra um erro genérico no rodapé do formulário
                    const errorMessage = err?.error || err.message || 'Ocorreu um erro. Tente novamente.';
                    showError(registerForm, errorMessage);
                }
            }
        });
    }
});
</script>