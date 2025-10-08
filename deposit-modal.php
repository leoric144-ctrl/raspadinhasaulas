<?php // deposit-modal.php ?>

<div id="dep-overlay" class="dep-overlay"></div>

<div id="dep-modal" class="dep-modal" role="dialog" aria-hidden="true">
    <button type="button" id="dep-close" class="dep-close" aria-label="Fechar">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" stroke="currentColor" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
    </button>

    <div id="dep-copy-popup" class="dep-copy-popup">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
        <span>Código Copiado!</span>
    </div>

    <div id="deposit-step-form">
        <div class="dep-hero">
            <img src="https://ik.imagekit.io/kyjz2djk3p/deposito3x.png?updatedAt=1757428651160" alt="Depositar" class="dep-hero-img">
            <div class="dep-hero-mask"></div>
        </div>
        <div class="dep-header">
            <svg fill="none" viewBox="0 0 24 24" width="28" height="28" xmlns="http://www.w3.org/2000/svg"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 15v3m0 3v-3m0 0h-3m3 0h3"></path><path fill="currentColor" fill-rule="evenodd" d="M5 5a3 3 0 0 0-3 3v8a3 3 0 0 0 3 3h7.083A6 6 0 0 0 12 18c0-1.148.322-2.22.881-3.131A3 3 0 0 1 9 12a3 3 0 1 1 5.869.881A5.97 5.97 0 0 1 18 12c1.537 0 2.939.578 4 1.528V8a3 3 0 0 0-3-3zm7 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2z" clip-rule="evenodd"></path></svg>
            <h1>Depositar</h1>
        </div>
        <form id="dep-form" class="dep-form">
            <label for="dep-amount" class="dep-label">Valor:</label>
            <div class="dep-input-wrap">
                <span class="dep-prefix">R$</span>
                <input id="dep-amount" name="amount" type="tel" inputmode="numeric" autocomplete="off" value="20,00">
            </div>
            <div class="dep-min" id="dep-min-msg" style="display: none;">O valor mínimo é R$ 20,00</div>
            <div class="dep-min" id="dep-max-msg" style="display: none;">O valor máximo é R$ 700,00</div>
            <div class="dep-quick">
                <button type="button" class="dep-qbtn" data-value="20">R$ 20,00</button>
                <button type="button" class="dep-qbtn dep-qbtn--hot" data-value="50">
                    <span class="dep-hot-badge"><svg fill="currentColor" width="10" height="10" viewBox="0 0 16 16"><path d="M8 16c3.314 0 6-2 6-5.5 0-1.5-.5-4-2.5-6 .25 1.5-1.25 2-1.25 2C11 4 9 .5 6 0c.357 2 .5 4-2 6-1.25 1-2 2.729-2 4.5C2 14 4.686 16 8 16m0-1c-1.657 0-3-1-3-2.75 0-.75.25-2 1.25-3C6.125 10 7 8.5 7 8.5c0-1 .5-3-1.5-5 .5 1.5-1 2.5-1 2.5C3 7.5 4 10.5 4 11.25C4 13 5.343 14 7 14c.75 0 1.5-.5 2-1 .5-.5 1-1.5 1-2C11 9.5 10 7.5 9 6c.5 1 .5 3 2.5 4C13.125 11 14 12.5 14 13.25c0 1.75-1.343 2.75-3 2.75z"/></svg>QUENTE</span>
                    R$ 50,00
                </button>
                <button type="button" class="dep-qbtn" data-value="100">R$ 100,00</button>
                <button type="button" class="dep-qbtn" data-value="200">R$ 200,00</button>
            </div>

            <label class="dep-label" style="margin-top: 1rem;">Método de Depósito:</label>

            <div class="modern-dropdown">
                <input type="hidden" id="dep-api-select" name="api" value="">
                <div class="dropdown-trigger">
                    <span>Carregando métodos...</span>
                </div>
                <div class="dropdown-panel" id="payment-methods-panel">
                    </div>
            </div>
            <button type="submit" class="dep-submit">
                <span class="dep-submit-text">Gerar QR Code</span>
            </button>
        </form>
    </div>

    <div id="deposit-step-pix" style="display: none;">
        <div class="pix-view-header">
            <h2>Depositar</h2>
            <p>Escaneie o QR Code abaixo usando o app do seu banco para realizar o pagamento</p>
            <p>Mantenha esse pop-up aberto para que possamos confirmar o pagamento automaticamente.</p>
        </div>
        <div class="pix-view-content">
            <div id="pix-qr-code-container" class="pix-qr-code">
                <img id="pix-qr-code-img" src="" alt="PIX QR Code" style="display:none;">
                <canvas id="pix-qr-code-canvas"></canvas>
            </div>
            <div class="pix-details-box">
                <div class="pix-value" id="pix-value"></div>
                <div class="pix-copy-wrapper">
                    <input type="text" id="pix-copy-paste-code" readonly>
                </div>
                <button id="pix-copy-button" class="pix-copy-btn">
                    <svg fill="currentColor" width="16" height="16" viewBox="0 0 16 16"><path d="M13 0H6a2 2 0 0 0-2 2 2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h7a2 2 0 0 0 2-2 2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zM4 4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1zm10 2a1 1 0 0 1-1 1H6V5h8a1 1 0 0 1 1 1z"/></svg>
                    <span>Copiar Código</span>
                </button>
            </div>
            <div class="pix-timer-wrapper">
                <p>O QR Code expira em: <strong id="pix-timer">10:00</strong></p>
                <div class="pix-timer-bar"><div id="pix-timer-progress"></div></div>
            </div>
        </div>
    </div>

    <div id="deposit-step-success" style="display: none;">
        <div class="payment-success-animation">
            <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52"><circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/><path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/></svg>
        </div>
        <h2>Pagamento Aprovado!</h2>
        <p>Seu saldo será atualizado em breve.</p>
    </div>

    <div id="deposit-step-expired" style="display: none;">
        <div class="payment-success-animation">
            <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52"><circle class="checkmark-circle checkmark-circle--error" cx="26" cy="26" r="25" fill="none"/><path class="checkmark-check checkmark-check--error" fill="none" d="M16 16 36 36 M36 16 16 36"/></svg>
        </div>
        <h2>PIX Expirado</h2>
        <p>Este QR Code não é mais válido. Por favor, gere um novo.</p>
    </div>
</div>

<style>
/* SEU BLOCO DE CSS COMPLETO E SEM ALTERAÇÕES VAI AQUI */
.dep-overlay{position:fixed;inset:0;background:rgba(0,0,0,.75);opacity:0;visibility:hidden;transition:.25s;z-index:9998;}
.dep-overlay.show{opacity:1;visibility:visible;}
.dep-modal{position:fixed;top:50%;left:50%;transform:translate(-50%,-50%) scale(.95);background:#111;border:1px solid var(--border);border-radius:12px;width:100%;max-width:480px;max-height:92vh;overflow:auto;z-index:9999;opacity:0;visibility:hidden;transition:.25s;color:#fff;}
.dep-modal.show{opacity:1;visibility:visible;transform:translate(-50%,-50%) scale(1);}
.dep-close{position:absolute;top:14px;right:16px;background:none;border:0;color:#ccc;cursor:pointer;opacity:.7;transition:.15s;padding:0;z-index:10;}
.dep-submit{margin-top:1rem;background:var(--primary);color:#000;font-weight:700;font-size:1rem;border:0;border-radius:8px;padding:.9rem 1rem;display:flex;align-items:center;justify-content:center;gap:.5rem;cursor:pointer;transition:opacity .18s; width: 100%; position: relative; overflow: hidden; min-height: 50px;}
.dep-submit:disabled{opacity:0.6; cursor:wait;}
.dep-spinner {width: 24px; height: 24px; border: 3px solid rgba(0, 0, 0, 0.2); border-top-color: #000; border-radius: 50%; animation: dep-spin 1s linear infinite;}
@keyframes dep-spin { to { transform: rotate(360deg); } }
#deposit-step-form { padding:2.75rem 2.5rem 2rem; }
.dep-hero{margin:-2.75rem -2.5rem 1.25rem;position:relative;height:160px;}
.dep-hero-img{width:100%;height:100%;object-fit:cover;border-radius:12px 12px 0 0;}
.dep-hero-mask{position:absolute;inset:0;background:linear-gradient(to bottom,rgba(0,0,0,.1) 0%,rgba(0,0,0,.1) 80%,#111 100%);}
.dep-header{display:flex;align-items:center;gap:.5rem;margin-bottom:1rem;justify-content:center;}
.dep-header h1{font-size:1.5rem;font-weight:600;}
.dep-form{display:flex;flex-direction:column;gap:.9rem;}
.dep-label{font-weight:600;margin-bottom:.25rem;}
.dep-input-wrap{position:relative;--pfx:42px;}
.dep-prefix{position:absolute;left:12px;top:50%;transform:translateY(-50%);opacity:.8;font-weight:600;width:var(--pfx);pointer-events:none;}
#dep-amount{width:100%;border:1px solid var(--border);background:transparent;border-radius:8px;padding:10px 12px 10px calc(var(--pfx) + 18px);font-size:1rem;color:#fff;}
.dep-min{font-size:.78rem;color:#ff6b6b;margin-top:-4px;}
.dep-quick{display:grid;grid-template-columns: repeat(2, 1fr); gap:8px;margin-top:4px;}
.dep-qbtn{background:rgba(40,229,4,.12);color:var(--primary);font-weight:600;border-radius:8px;padding:.48rem .9rem;font-size:.9rem;border:0;cursor:pointer;transition:background .15s;position:relative;}
.dep-qbtn:hover{background:rgba(40,229,4,.2);}
.dep-qbtn--hot{box-shadow:0 0 0 2px #facc15 inset;}
.dep-hot-badge{position:absolute;top:-7px;left:50%;transform:translateX(-50%);background:#facc15;color:#000;font-size:.62rem;line-height:1;padding:2px 6px;border-radius:4px;display:flex;gap:4px;align-items:center;text-transform:uppercase; font-weight: 700;}
#deposit-step-pix { padding: 2rem; text-align: center; }
.pix-view-header h2 { font-size: 1.5rem; font-weight: 600; margin: 0 0 0.5rem; }
.pix-view-header p { font-size: 0.9rem; color: #a0a0a0; margin: 0 auto 1.5rem; max-width: 30ch; }
.pix-qr-code {margin-left: auto; margin-right: auto; max-width: 250px; width: 100%; aspect-ratio: 1/1; border-radius: 12px; background-color: white; padding: 1rem; box-sizing: border-box; display: flex; align-items: center; justify-content: center; overflow: hidden;}
/* INÍCIO DO AJUSTE DE CSS PARA PADRONIZAÇÃO DO QR CODE */
.pix-qr-code canvas, .pix-qr-code img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 0;
    box-sizing: border-box;
    display: block;
    margin: 0;
}
/* FIM DO AJUSTE DE CSS PARA PADRONIZAÇÃO DO QR CODE */
.pix-details-box { border: 2px dashed #333; border-radius: 8px; padding: 1rem; margin-top: 1.5rem; }
.pix-value { font-size: 1.75rem; font-weight: 700; color: var(--primary); }
.pix-copy-wrapper { margin-top: 1rem; }
#pix-copy-paste-code { width: 100%; background: #27272a; border: none; border-radius: 6px; color: #a0a0a0; padding: 0.75rem; font-size: 0.8rem; text-overflow: ellipsis; text-align: center; }
.pix-copy-btn { width: 100%; background: none; border: none; color: var(--primary); font-weight: 600; cursor: pointer; padding: 0.75rem; margin-top: 0.5rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem; }
.pix-timer-wrapper { margin: 1.5rem 0; }
.pix-timer-wrapper p { margin: 0 0 0.5rem; font-size: 0.8rem; color: #a0a0a0; }
.pix-timer-wrapper p strong { color: #f59e0b; font-size: 1rem; }
.pix-timer-bar { height: 6px; background-color: #333; border-radius: 99px; overflow: hidden; }
#pix-timer-progress { width: 100%; height: 100%; background-color: var(--primary); border-radius: 99px; transition: width 1s linear; }
#deposit-step-success, #deposit-step-expired { padding: 4rem 2rem; text-align: center; display: none; }
#deposit-step-success h2, #deposit-step-expired h2 { color: var(--primary); }
#deposit-step-expired h2 { color: #ff6b6b; }
#deposit-step-success p, #deposit-step-expired p { color: #a0a0a0; }
.payment-success-animation .checkmark-circle { stroke-dasharray: 166; stroke-dashoffset: 166; stroke-width: 2; stroke-miterlimit: 10; stroke: var(--primary); fill: none; animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards; }
.payment-success-animation .checkmark { width: 80px; height: 80px; border-radius: 50%; display: block; stroke-width: 3; stroke: #fff; stroke-miterlimit: 10; margin: 0 auto 1rem; box-shadow: inset 0px 0px 0px var(--primary); animation: fill-success .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both; }
.payment-success-animation .checkmark-check { transform-origin: 50% 50%; stroke-dasharray: 48; stroke-dashoffset: 48; animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards; }
.payment-success-animation .checkmark-circle--error { stroke: #ff6b6b; }
.payment-success-animation .checkmark--error { box-shadow: inset 0px 0px 0px 40px #ff6b6b; animation: fill-error .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both; }
.payment-success-animation .checkmark-check--error { stroke-dasharray: 80; stroke-dashoffset: 80; animation: stroke 0.4s ease-in-out 0.8s forwards; }
@keyframes stroke { 100% { stroke-dashoffset: 0; } }
@keyframes scale { 0%, 100% { transform: none; } 50% { transform: scale3d(1.1, 1.1, 1); } }
@keyframes fill-success { 100% { box-shadow: inset 0px 0px 0px 40px var(--primary); } }
@keyframes fill-error { 100% { box-shadow: inset 0px 0px 0px 40px #ff6b6b; } }
.modern-dropdown { position: relative; }
.dropdown-trigger {background: #27272a; border: 1px solid #3f3f46; border-radius: 8px; padding: 10px 14px; display: flex; align-items: center; cursor: pointer;}
.dropdown-trigger img { width: 24px; height: 24px; margin-right: 10px; border-radius: 16px; }
.dropdown-trigger span { font-weight: 600; }
.dropdown-arrow { margin-left: auto; color: #a0a0a0; transition: transform .2s; }
.modern-dropdown.open .dropdown-arrow { transform: rotate(180deg); }
.dropdown-panel {position: absolute; top: calc(100% + 4px); left: 0; right: 0; background: #27272a; border: 1px solid #3f3f46; border-radius: 8px; z-index: 10; opacity: 0; visibility: hidden; transform: translateY(-10px); transition: opacity .2s, transform .2s;}
.modern-dropdown.open .dropdown-panel { opacity: 1; visibility: visible; transform: translateY(0); }
.dropdown-option {padding: 10px 14px; display: flex; align-items: center; cursor: pointer;}
.dropdown-option:hover { background: #3f3f46; }
.dropdown-option:first-child { border-radius: 8px 8px 0 0; }
.dropdown-option:last-child { border-radius: 0 0 8px 8px; }
.dropdown-option img { width: 24px; height: 24px; margin-right: 10px; border-radius: 16px; }
.dropdown-option span { font-weight: 600; }
.dep-copy-popup {position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) scale(0.9); background-color: var(--primary); color: #000; padding: 15px 25px; border-radius: 10px; z-index: 10002; display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 1.1rem; box-shadow: 0 5px 20px rgba(0,0,0,0.4); opacity: 0; visibility: hidden; transition: opacity 0.2s, transform 0.2s, visibility 0s 0.2s;}
.dep-copy-popup.show {opacity: 1; visibility: visible; transform: translate(-50%, -50%) scale(1); transition: opacity 0.2s, transform 0.2s;}
@media(max-width:480px){.dep-modal{padding:0; max-width:calc(100% - 1.5rem);}.dep-hero{margin:-1.75rem -1.25rem 1rem; height:140px;} #deposit-step-form, #deposit-step-pix { padding:1.75rem 1.25rem; } }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
<script>
(() => {
    // Bloco de constantes e seletores de elementos
    const MIN_VALUE = 20;
    const MAX_VALUE = 700;
    const POLLING_INTERVAL_MS = 3000;
    const PIX_EXPIRY_SECONDS = 600;
    const modal = document.getElementById('dep-modal');
    const overlay = document.getElementById('dep-overlay');
    const closeBtn = document.getElementById('dep-close');
    const stepForm = document.getElementById('deposit-step-form');
    const form = document.getElementById('dep-form');
    const amountInput = document.getElementById('dep-amount');
    const quickButtons = document.querySelectorAll('.dep-qbtn');
    const depSubmitBtn = form.querySelector('.dep-submit');
    const depSubmitBtnText = depSubmitBtn.querySelector('.dep-submit-text');
    const stepPix = document.getElementById('deposit-step-pix');
    const copyInput = document.getElementById('pix-copy-paste-code');
    const dropdown = document.querySelector('.modern-dropdown');
    const hiddenInputApi = document.getElementById('dep-api-select');
    const dropdownTrigger = dropdown.querySelector('.dropdown-trigger');
    const qrContainer = document.getElementById('pix-qr-code-container');
    const qrImg = document.getElementById('pix-qr-code-img');
    const qrCanvas = document.getElementById('pix-qr-code-canvas');
    const copyButton = document.getElementById('pix-copy-button');
    const copyPopup = document.getElementById('dep-copy-popup');
    const minMsg = document.getElementById('dep-min-msg');
    const maxMsg = document.getElementById('dep-max-msg');

    // NOVO: Seleciona o painel de métodos de pagamento pelo ID que adicionamos no HTML
    const paymentMethodsPanel = document.getElementById('payment-methods-panel');
    const arrowSVG = `<svg class="dropdown-arrow" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>`;

    let pixTimerInterval = null;
    let pollingInterval = null;
    let copyPopupTimeout = null;

    const profileCompletionMessage = document.createElement('div');
    profileCompletionMessage.id = 'profile-completion-msg';
    profileCompletionMessage.style.cssText = `background-color: #ff6b6b; color: #fff; padding: 10px; border-radius: 8px; text-align: center; margin-top: 15px; display: none; font-size: 0.9em; font-weight: 600;`;
    form.insertBefore(profileCompletionMessage, depSubmitBtn);

    const moneyToNumber = str => Number(String(str).replace(/\./g, '').replace(',', '.')) || 0;
    const formatBRL = (value) => {
        let numStr = String(value).replace(/\D/g, '');
        if (numStr === '') return '';
        if (numStr.length > 1) { numStr = numStr.replace(/^0+/, ''); }
        numStr = numStr.padStart(3, '0');
        let intPart = numStr.slice(0, -2);
        let decPart = numStr.slice(-2);
        intPart = intPart.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
        return `${intPart},${decPart}`;
    };

    // NOVO: Função para carregar e construir o dropdown de métodos de pagamento
    async function loadPaymentMethods() {
        try {
            depSubmitBtn.disabled = true;
            dropdownTrigger.innerHTML = `<span>Carregando...</span> ${arrowSVG}`;

            const response = await fetch('/api/get_deposit_methods.php');
            const data = await response.json();

            if (!data.ok || !data.methods || data.methods.length === 0) {
                dropdownTrigger.innerHTML = `<span>Nenhum método disponível</span>`;
                profileCompletionMessage.textContent = 'Não há métodos de depósito disponíveis no momento. Tente novamente mais tarde.';
                profileCompletionMessage.style.display = 'block';
                return;
            }

            paymentMethodsPanel.innerHTML = ''; // Limpa as opções antigas

            data.methods.forEach(method => {
                const optionHTML = `
                    <div class="dropdown-option" data-value="${method.provider_key}">
                        <img src="${method.icon_url || ''}" alt="${method.name}">
                        <span>${method.name}</span>
                    </div>`;
                paymentMethodsPanel.insertAdjacentHTML('beforeend', optionHTML);
            });

            // Define o primeiro método da lista como o padrão
            const firstMethod = data.methods[0];
            hiddenInputApi.value = firstMethod.provider_key;
            dropdownTrigger.innerHTML = `
                <img src="${firstMethod.icon_url || ''}" alt="${firstMethod.name}">
                <span>${firstMethod.name}</span>
                ${arrowSVG}`;

            depSubmitBtn.disabled = false;

        } catch (error) {
            console.error("Erro ao carregar métodos de pagamento:", error);
            dropdownTrigger.innerHTML = `<span>Erro ao carregar</span>`;
            profileCompletionMessage.textContent = 'Não foi possível carregar os métodos de pagamento.';
            profileCompletionMessage.style.display = 'block';
        }
    }

    // ALTERADO: A lógica de clique agora usa "event delegation", funcionando com itens dinâmicos
    dropdownTrigger.addEventListener('click', () => dropdown.classList.toggle('open'));

    paymentMethodsPanel.addEventListener('click', (e) => {
        const option = e.target.closest('.dropdown-option');
        if (!option) return;

        hiddenInputApi.value = option.dataset.value;
        dropdownTrigger.innerHTML = option.innerHTML + arrowSVG;
        dropdown.classList.remove('open');
    });

    amountInput.addEventListener('input', (e) => {
        const input = e.target;
        const formattedValue = formatBRL(input.value);
        if (input.value !== formattedValue) {
            const originalStart = input.selectionStart;
            const originalLength = input.value.length;
            input.value = formattedValue;
            const newLength = input.value.length;
            input.setSelectionRange(originalStart + (newLength - originalLength), originalStart + (newLength - originalLength));
        }
    });

    quickButtons.forEach(button => {
        button.addEventListener('click', () => {
            amountInput.value = formatBRL(button.dataset.value + '00');
            minMsg.style.display = 'none';
            maxMsg.style.display = 'none';
            profileCompletionMessage.style.display = 'none';
        });
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const amount = moneyToNumber(amountInput.value);
        if (amount < MIN_VALUE) {
            minMsg.style.display = 'block'; setTimeout(() => { minMsg.style.display = 'none'; }, 3000); return;
        }
        if (amount > MAX_VALUE) {
            maxMsg.style.display = 'block'; setTimeout(() => { maxMsg.style.display = 'none'; }, 3000); return;
        }

        profileCompletionMessage.style.display = 'none';
        depSubmitBtn.disabled = true;
        if(depSubmitBtnText) depSubmitBtnText.style.display = 'none';
        const spinner = document.createElement('div');
        spinner.className = 'dep-spinner';
        depSubmitBtn.appendChild(spinner);

        try {
            const profileCheckResponse = await fetch('/api/verificar-perfil.php');
            const profileCheckData = await profileCheckResponse.json();
            if (!profileCheckResponse.ok || !profileCheckData.success) {
                throw new Error(profileCheckData.message || 'Erro ao verificar perfil.');
            }
            if (!profileCheckData.profile_complete) {
                profileCompletionMessage.textContent = profileCheckData.message || 'Para depositar, seu perfil precisa estar completo.';
                profileCompletionMessage.style.display = 'block';
                setTimeout(() => { window.location.href = 'perfil.php'; }, 3000);
                return;
            }

            const response = await fetch('/api/gerar-pix.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ amount: Math.round(amount * 100), api: hiddenInputApi.value })
            });
            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.error || `Erro ${response.status}: Falha ao gerar PIX.`);
            }
            showPixScreen(data, amount);
            startPolling(data.transactionId);

        } catch (error) {
            profileCompletionMessage.textContent = `Erro: ${error.message}`;
            profileCompletionMessage.style.display = 'block';
            setTimeout(() => {
                profileCompletionMessage.style.display = 'none';
                profileCompletionMessage.textContent = '';
            }, 5000);
        } finally {
            if (getComputedStyle(stepForm).display !== 'none') {
                depSubmitBtn.disabled = false;
                if (depSubmitBtn.contains(spinner)) depSubmitBtn.removeChild(spinner);
                if(depSubmitBtnText) depSubmitBtnText.style.display = 'inline';
            }
        }
    });

    function showCopyPopup() {
        clearTimeout(copyPopupTimeout);
        copyPopup.classList.add('show');
        copyPopupTimeout = setTimeout(() => {
            copyPopup.classList.remove('show');
        }, 2000);
    }

    function showPixScreen(pixData, amount) {
        document.getElementById('pix-value').textContent = `R$ ${amount.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
        copyInput.value = pixData.pix_copy_paste_code;

        if (pixData.qr_code_base64) {
            qrImg.src = pixData.qr_code_base64;
            qrImg.style.display = 'block';
            qrCanvas.style.display = 'none';
        } else {
            qrImg.style.display = 'none';
            qrCanvas.style.display = 'block';
            qrCanvas.getContext('2d').clearRect(0, 0, qrCanvas.width, qrCanvas.height); // Limpa o canvas anterior
            // AJUSTE JS: Aumenta o size do QRious para alta resolução, o CSS cuida da exibição.
            new QRious({ element: qrCanvas, value: pixData.pix_copy_paste_code, size: 500, padding: 0, level: 'H' });
        }

        stepForm.style.display = 'none';
        document.getElementById('deposit-step-success').style.display = 'none';
        document.getElementById('deposit-step-expired').style.display = 'none';
        stepPix.style.display = 'block';
        startTimer();
    }

    function startPolling(transactionId) {
        clearInterval(pollingInterval);
        pollingInterval = setInterval(async () => {
            try {
                const response = await fetch(`/api/verificar-pix.php?id=${transactionId}`);
                if (!response.ok) return;
                const data = await response.json();
                if (data.status === 'PAID') {
                    showSuccessScreen();
                }
            } catch (error) { console.error("Erro ao verificar pagamento:", error); }
        }, POLLING_INTERVAL_MS);
    }

    function showSuccessScreen() {
        clearInterval(pollingInterval);
        clearInterval(pixTimerInterval);
        stepPix.style.display = 'none';
        document.getElementById('deposit-step-expired').style.display = 'none';
        document.getElementById('deposit-step-success').style.display = 'block';
        setTimeout(() => {
            closeDepositModal();
            window.location.reload();
        }, 2500);
    }

    function showExpiredScreen() {
        clearInterval(pollingInterval);
        clearInterval(pixTimerInterval);
        stepPix.style.display = 'none';
        document.getElementById('deposit-step-success').style.display = 'none';
        document.getElementById('deposit-step-expired').style.display = 'block';
        setTimeout(closeDepositModal, 3000);
    }

    function closeDepositModal() {
        overlay.classList.remove('show');
        modal.classList.remove('show');
        clearInterval(pixTimerInterval);
        clearInterval(pollingInterval);
    }

    function startTimer() {
        const timerDisplay = document.getElementById('pix-timer');
        const timerProgress = document.getElementById('pix-timer-progress');
        clearInterval(pixTimerInterval);
        let timeLeft = PIX_EXPIRY_SECONDS;
        timerProgress.style.transition = 'none';
        timerProgress.style.width = '100%';

        setTimeout(() => {
            timerProgress.style.transition = `width 1s linear`;
            const updateTimer = () => {
                if (timeLeft < 0) {
                    showExpiredScreen();
                    clearInterval(pixTimerInterval);
                    return;
                }
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                timerDisplay.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                timerProgress.style.width = `${(timeLeft / PIX_EXPIRY_SECONDS) * 100}%`;
                timeLeft--;
            };
            pixTimerInterval = setInterval(updateTimer, 1000);
            updateTimer();
        }, 50);
    }

    copyButton.addEventListener('click', () => {
        navigator.clipboard.writeText(copyInput.value).then(() => {
            showCopyPopup();
        });
    });

    window.openDepositModal = function() {
        stepForm.style.display = 'block';
        stepPix.style.display = 'none';
        document.getElementById('deposit-step-success').style.display = 'none';
        document.getElementById('deposit-step-expired').style.display = 'none';

        const spinner = depSubmitBtn.querySelector('.dep-spinner');
        if(spinner) depSubmitBtn.removeChild(spinner);
        if(depSubmitBtnText) depSubmitBtnText.style.display = 'inline';

        profileCompletionMessage.style.display = 'none';
        amountInput.value = formatBRL('2000');
        clearInterval(pixTimerInterval);
        clearInterval(pollingInterval);

        // NOVO: Carrega os métodos de pagamento sempre que o modal é aberto
        loadPaymentMethods();

        overlay.classList.add('show');
        modal.classList.add('show');
    };

    closeBtn.addEventListener('click', closeDepositModal);
    overlay.addEventListener('click', closeDepositModal);
    document.addEventListener('click', (e) => {
        if (!dropdown.contains(e.target)) { dropdown.classList.remove('open'); }
    });

    amountInput.value = formatBRL('2000');
})();
</script>