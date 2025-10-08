<?php
// public/withdraw-modal.php - Versão final com correção no dropdown de saldo e opção de transferência para saldo principal
?>
<div id="wd-overlay" class="wd-overlay"></div>
<div id="wd-modal" class="wd-modal" aria-hidden="true" role="dialog">
    <button class="wd-close" id="wd-close" aria-label="Fechar">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
    </button>
    <div id="wd-error-popup" class="wd-error-popup">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
        <span id="wd-error-popup-message"></span>
    </div>
    <div class="wd-hero">
        <img src="https://ik.imagekit.io/azx3nlpdu/SAQUE.jpg?updatedAt=1751798026776" alt="">
        <div class="wd-hero-mask"></div>
    </div>
    <div class="wd-header">
        <svg viewBox="0 0 24 24" fill="currentColor" width="24" height="24"><path d="M22 2H2a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h3v9a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-9h3a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1ZM7 20v-2a2 2 0 0 1 2 2Zm10 0h-2a2 2 0 0 1 2-2Zm0-4a4 4 0 0 0-4 4h-2a4 4 0 0 0-4-4V8h10Zm4-6h-2V7a1 1 0 0 0-1-1H6a1 1 0 0 0-1 1v3H3V4h18Zm-9 5a3 3 0 1 0-3-3 3 3 0 0 0 3 3Zm0-4a1 1 0 1 1-1 1 1 1 0 0 1 1-1Z"/></svg>
        <h1>Sacar</h1>
    </div>
    <form id="wd-form" class="wd-form">
        <div class="loading-message" id="wd-loading-data" style="text-align: center; color: #888; margin-bottom: 15px;">Carregando seus dados...</div>
        <label class="wd-label">Sacar de:</label>
        <div id="balance-dropdown" class="modern-dropdown">
            <input type="hidden" id="balance-source-select" value="main">
            <div class="dropdown-trigger">
                <span>Saldo Principal</span>
                <svg class="dropdown-arrow" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
            </div>
            <div class="dropdown-panel">
                <div class="dropdown-option" data-value="main">
                    <span>Saldo Principal</span>
                </div>
                <div class="dropdown-option" data-value="commission">
                    <span>Saldo de Comissão</span>
                </div>
            </div>
        </div>
        <div class="wd-current-balance">Saldo disponível: <span id="current-balance-display">R$ --,--</span></div>
        <div id="wd-commission-actions" style="display:none; flex-direction:column; gap:.85rem;">
            <label class="wd-label">Opção de Saque:</label>
            <div id="commission-action-dropdown" class="modern-dropdown">
                <input type="hidden" id="commission-action-select" value="pix">
                <div class="dropdown-trigger">
                    <span>Sacar para PIX</span>
                    <svg class="dropdown-arrow" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </div>
                <div class="dropdown-panel">
                    <div class="dropdown-option" data-value="pix">
                        <span>Sacar para PIX</span>
                    </div>
                    <div class="dropdown-option" data-value="main_balance">
                        <span>Transferir para Saldo Principal</span>
                    </div>
                </div>
            </div>
        </div>
        <div id="wd-value-section">
            <label class="wd-label" for="wd-amount">Valor:</label>
            <div class="wd-input-wrap">
                <span class="wd-prefix">R$&nbsp;</span>
                <input id="wd-amount" type="tel" inputmode="numeric" autocomplete="off" value="0,00" class="wd-input">
            </div>
            <div class="wd-min hide" id="wd-min-msg"></div>
            <div class="wd-chips">
                <button type="button" class="wd-chip" data-v="20">R$ 20</button>
                <button type="button" class="wd-chip" data-v="100">R$ 100</button>
                <button type="button" class="wd-chip" data-v="200">R$ 200</button>
                <button type="button" class="wd-chip" data-v="700">R$ 700</button>
            </div>
        </div>
        <div id="wd-pix-section">
            <label class="wd-label" for="wd-key">Chave PIX</label>
            <div class="wd-pix-wrap">
                <button type="button" id="wd-pix-trigger" class="wd-select-btn">
                    <span id="wd-pix-label">Telefone</span>
                    <svg class="dropdown-arrow" xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <div id="wd-pix-menu" class="wd-pix-menu" role="listbox">
                    <button type="button" data-type="phone">Telefone</button>
                    <button type="button" data-type="email">Email</button>
                    <button type="button" data-type="cpf">CPF</button>
                    <button type="button" data-type="random">Chave aleatória</button>
                    <button type="button" data-type="cnpj">CNPJ</button>
                </div>
                <input id="wd-key-type" type="hidden" value="phone">
                <input id="wd-key" type="text" placeholder="Digite sua chave PIX..." class="wd-input">
            </div>
            <label class="wd-label" for="wd-cpf">CPF</label>
            <div class="wd-input-wrap icon-left">
                <svg width="16" height="16" viewBox="0 0 24 24" class="wd-icon-left" fill="currentColor"><path fill-rule="evenodd" d="M4.5 3.75a3 3 0 0 0-3 3v10.5a3 3 0 0 0 3 3h15a3 3 0 0 0 3-3V6.75a3 3 0 0 0-3-3zm4.125 3a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5Zm-3.873 8.703a4.126 4.126 0 0 1 7.746 0 .75.75 0 0 1-.351.92 7.5 7.5 0 0 1-3.522.877 7.5 7.5 0 0 1-3.522-.877.75.75 0 0 1-.351-.92ZM15 8.25a.75.75 0 0 0 0 1.5h3.75a.75.75 0 0 0 0-1.5zM14.25 12a.75.75 0 0 1 .75-.75h3.75a.75.75 0 0 1 0 1.5H15a.75.75 0 0 1-.75-.75Zm.75 2.25a.75.75 0 0 0 0 1.5h3.75a.75.75 0 0 0 0-1.5z" clip-rule="evenodd"/></svg>
                <input id="wd-cpf" type="text" placeholder="000.000.000-00" inputmode="numeric" class="wd-input pl-8" readonly>
            </div>
            <div class="wd-fees-info">
                <p>Valor da taxa de saque: <strong id="withdraw-fee-display">R$ 6,90</strong></p>
            </div>
            <p class="wd-alert-text">
                <i class="bi bi-info-circle-fill"></i> Importante: A conta de destino da chave PIX deve ser da mesma titularidade do CPF cadastrado na plataforma.<br><br>Caso contrário, o saque será negado.
            </p>
        </div>
        <p class="wd-alert-text hide" id="wd-rollover-warning">
            <i class="bi bi-info-circle-fill"></i> Ao transferir para o Saldo Principal, será aplicado um rollover de 1x sobre o valor.<br><br>O valor só poderá ser sacado após ser apostado.
        </p>
        <button type="submit" class="wd-submit">
            <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M22 2H2a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h3v9a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-9h3a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1ZM7 20v-2a2 2 0 0 1 2 2Zm10 0h-2a2 2 0 0 1 2-2Zm0-4a4 4 0 0 0-4 4h-2a4 4 0 0 0-4-4V8h10Zm4-6h-2V7a1 1 0 0 0-1-1H6a1 1 0 0 0-1 1v3H3V4h18Zm-9 5a3 3 0 1 0-3-3 3 3 0 0 0 3 3Zm0-4a1 1 0 1 1-1 1 1 1 0 0 1 1-1Z"/></svg>
            Solicitar Saque
            <span class="wd-btn-glow"></span>
        </button>
    </form>
</div>
<div id="wd-success-overlay" class="wd-success-modal">
    <div class="wd-success-modal-content">
        <i class="bi bi-check-circle-fill icon-success"></i>
        <h2 id="wd-success-title">Saque Solicitado!</h2>
        <p id="wd-success-message">Seu saque de R$ <span id="wd-final-amount"></span> foi solicitado com sucesso! Ele está em análise e o prazo máximo para aprovação é de até 30 minutos.</p>
    </div>
</div>
<style>
/* Bloco de CSS Completo e sem abreviações */
.wd-overlay{position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:9998;opacity:0;visibility:hidden;transition:.25s;}
.wd-overlay.show{opacity:1;visibility:visible;}
.wd-modal{position:fixed;top:50%;left:50%;transform:translate(-50%,-50%) scale(.95);background:#111;border:1px solid var(--border);border-radius:12px;z-index:9999;width:100%;max-width:450px;max-height:85vh;overflow-y:auto;padding:2.5rem 2rem 2.75rem;opacity:0;visibility:hidden;transition:.25s;}
.wd-modal.show{opacity:1;visibility:visible;transform:translate(-50%,-50%) scale(1);}
.wd-close{position:absolute;top:14px;right:14px;background:none;border:0;color:#bbb;cursor:pointer;transition:opacity .2s;}
.wd-close:hover{opacity:.8;}
.wd-hero{position:relative;margin:-2.5rem -2rem 1.25rem;}
.wd-hero img{width:100%;display:block;border-radius:12px 12px 0 0;object-fit:cover;}
.wd-hero-mask{position:absolute;inset:0;background:linear-gradient(to bottom,rgba(0,0,0,.12) 0%,rgba(0,0,0,.12) 85%,#111 100%);}
.wd-header{display:flex;align-items:center;gap:.5rem;margin-bottom:1rem;}
.wd-header h1{font-size:1.45rem;font-weight:600;}
.wd-form{display:flex;flex-direction:column;gap:.85rem;}
.wd-label{font-weight:600;margin-bottom:.25rem;}
.wd-input-wrap{position:relative;--prefix-w:46px;}
.wd-prefix{position:absolute;left:12px;top:50%;transform:translateY(-50%);opacity:.8;font-weight:600;width:var(--prefix-w);pointer-events:none;white-space:nowrap;}
.wd-input{width:100%;border:1px solid var(--border);background:transparent;border-radius:8px;padding:10px 12px;padding-left:calc(var(--prefix-w) + 16px);font-size:1rem;color:#fff;}
.wd-input.pl-8{padding-left: 2.5rem;}
.wd-input[readonly]{background-color: #222;opacity: 0.7;cursor: not-allowed;}
.wd-input:focus{outline:none;border-color:var(--primary);}
.wd-min{font-size:.75rem;color:#ff6b6b;margin-top:2px;}
.wd-min.hide{display:none;}
.wd-chips{display:flex;flex-wrap:wrap;gap:8px;margin-top:9px;justify-content: center;}
.wd-chip{background:rgba(40,229,4,.12);color:var(--primary);font-weight:600;border-radius:8px;padding:.5rem 1rem;font-size:.9rem;border:0;cursor:pointer;transition:background .15s;}
.wd-chip:hover{background:rgba(40,229,4,.2);}
.wd-pix-wrap{position:relative;display:flex;gap:6px;align-items:center;}
.wd-select-btn{border:1px solid var(--border);background:transparent;color:#fff;border-radius:8-px;padding:8px 10px;font-size:.85rem;display:flex;align-items:center;gap:6px;cursor:pointer;}
.wd-pix-menu{position:absolute;top:100%;left:0;background:#111;border:1px solid var(--border);border-radius:8px;box-shadow:0 10px 25px rgba(0,0,0,.45);padding:4px 0;opacity:0;visibility:hidden;transform:translateY(-6px);transition:.18s;z-index:5;min-width:140px;}
.wd-pix-menu.show{opacity:1;visibility:visible;transform:translateY(0);}
.wd-pix-menu button{width:100%;text-align:left;padding:8px 12px;background:none;border:0;color:#fff;font-size:.85rem;cursor:pointer;}
.wd-pix-menu button:hover{background:#222;}
.wd-icon-left{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#888;pointer-events:none;}
.wd-submit{margin-top:1rem;background:var(--primary);color:#000;font-weight:700;font-size:1rem;border:0;border-radius:8px;padding:.9rem 1rem;display:flex;align-items:center;justify-content:center;gap:.5rem;position:relative;overflow:hidden;cursor:pointer;transition:opacity .18s;}
.wd-submit:hover{opacity:.9;}
.wd-btn-glow{position:absolute;left:0;top:0;bottom:0;width:0;background:rgba(0,0,0,.2);transition:width .7s;}
.wd-submit:hover .wd-btn-glow{width:100%;}
.wd-alert-text {font-size: 0.85rem;color: #FFC107;background-color: rgba(255, 193, 7, 0.15);border: 1px solid rgba(255, 193, 7, 0.4);border-radius: 8px;padding: 10px 15px;display: flex;align-items: center;gap: 8px;margin-top: 10px;}
.wd-alert-text i {font-size: 1.2rem;flex-shrink: 0;}
.wd-current-balance {font-size: 1rem;font-weight: 600;color: #FFF;text-align: center;margin-bottom: 15px;padding-bottom: 10px;border-bottom: 1px dashed rgba(255,255,255,0.1);}
.wd-fees-info {text-align: center;font-size: 0.9rem;color: #aaa;}
.wd-success-modal {position: fixed; top: 0; left: 0; width: 100%; height: 100%;background-color: rgba(0,0,0,.7); display: flex; justify-content: center;align-items: center; z-index: 10000; opacity: 0; visibility: hidden;transition: opacity 0.5s ease-out, visibility 0s linear 0.5s;}
.wd-success-modal.show {opacity: 1; visibility: visible; transition: opacity 0.5s ease-in;}
.wd-success-modal-content {background-color: #1a1a1a; border: 1px solid var(--primary); border-radius: 12px;padding: 30px; width: 90%; max-width: 400px; text-align: center;transform: scale(0.9); transition: transform 0.3s ease-out;}
.wd-success-modal.show .wd-success-modal-content {transform: scale(1);}
.wd-success-modal-content .icon-success {font-size: 4rem; color: var(--primary); margin-bottom: 20px;}
.wd-success-modal-content h2 {color: #fff; font-size: 1.6rem; margin-bottom: 15px;}
.wd-success-modal-content p {color: #ccc; font-size: 1rem; line-height: 1.5;}
.wd-error-popup {position: absolute; top: 50%; left: 50%;transform: translate(-50%, -50%) scale(0.9);background-color: #E24C4C; color: #fff;padding: 15px 20px; border-radius: 10px; z-index: 10001;display: flex; align-items: center; gap: 10px; font-weight: 600;box-shadow: 0 5px 20px rgba(0,0,0,0.4); opacity: 0;visibility: hidden; transition: opacity 0.2s, transform 0.2s, visibility 0s 0.2s;}
.wd-error-popup.show {opacity: 1; visibility: visible; transform: translate(-50%, -50%) scale(1);transition: opacity 0.2s, transform 0.2s;}
.modern-dropdown { position: relative; }
.dropdown-trigger {background: #222; border: 1px solid var(--border); border-radius: 8px;padding: 10px 14px; display: flex; align-items: center; cursor: pointer;justify-content: space-between;}
.dropdown-trigger span { font-weight: 600; }
.dropdown-arrow { color: #a0a0a0; transition: transform .2s; }
.modern-dropdown.open .dropdown-arrow { transform: rotate(180deg); }
.dropdown-panel {position: absolute; top: calc(100% + 4px); left: 0; right: 0;background: #222; border: 1px solid var(--border); border-radius: 8px;z-index: 10; opacity: 0; visibility: hidden; transform: translateY(-10px);transition: opacity .2s, transform .2s; padding: 4px;}
.modern-dropdown.open .dropdown-panel { opacity: 1; visibility: visible; transform: translateY(0); }
.dropdown-option {padding: 10px 14px; display: flex; align-items: center; cursor: pointer;font-weight: 600; border-radius: 6px;}
.dropdown-option:hover { background: #333; }
#wd-pix-section.hide { display: none !important; }
#wd-rollover-warning.hide { display: none !important; }
@media(max-width:480px){.wd-modal{padding:1.75rem 1.25rem 2rem;max-width:calc(100% - 2rem);}.wd-hero{margin:-1.75rem -1.25rem 1rem;}}
</style>
<script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
<script>
(() => {
    const WITHDRAWAL_FEE_CENTS = 690;
    const MIN_WITHDRAW_VALUE_CENTS = 30 * 100;
    const MAX_WITHDRAW_VALUE_CENTS = 10000 * 100;
    const ov = document.getElementById('wd-overlay');
    const mod = document.getElementById('wd-modal');
    const closeBtn = document.getElementById('wd-close');
    const form = document.getElementById('wd-form');
    const amountInput = document.getElementById('wd-amount');
    const chips = document.querySelectorAll('.wd-chip');
    const cpfInput = document.getElementById('wd-cpf');
    const minMsg = document.getElementById('wd-min-msg');
    const currentBalanceDisplay = document.getElementById('current-balance-display');
    const loadingDataMessage = document.getElementById('wd-loading-data');
    const errorPopup = document.getElementById('wd-error-popup');
    const errorMessageSpan = document.getElementById('wd-error-popup-message');
    const balanceDropdown = document.getElementById('balance-dropdown');
    const balanceHiddenInput = document.getElementById('balance-source-select');
    const balanceTrigger = balanceDropdown.querySelector('.dropdown-trigger');
    const commissionActionsSection = document.getElementById('wd-commission-actions');
    const commissionActionDropdown = document.getElementById('commission-action-dropdown');
    const commissionActionHiddenInput = document.getElementById('commission-action-select');
    const commissionActionTrigger = commissionActionDropdown.querySelector('.dropdown-trigger');
    const pixSection = document.getElementById('wd-pix-section');
    const rolloverWarning = document.getElementById('wd-rollover-warning');
    const pixTrigger = document.getElementById('wd-pix-trigger');
    const pixMenu = document.getElementById('wd-pix-menu');
    const pixHidden = document.getElementById('wd-key-type');
    const pixLabel = document.getElementById('wd-pix-label');
    const pixKeyInput = document.getElementById('wd-key');
    const successOverlay = document.getElementById('wd-success-overlay');
    const successAmountSpan = document.getElementById('wd-final-amount');
    let USER_DOCUMENT_RAW = '';
    let USER_EMAIL = '';
    let USER_PHONE_RAW = '';
    let USER_MAIN_BALANCE_CENTS = 0;
    let USER_COMMISSION_BALANCE_CENTS = 0;
    let errorPopupTimeout;
    const formatBRL = (centsValue) => {
        const value = (centsValue || 0) / 100;
        return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
    };
    const digitsToMoney = (digits) => {
        digits = (digits || '0').replace(/\D/g, '').replace(/^0+(?=\d{3,})/, '');
        while (digits.length < 3) digits = '0' + digits;
        const ints = digits.slice(0, -2) || '0';
        const cents = digits.slice(-2);
        return '' + ints.replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ',' + cents;
    };
    const moneyToCents = (str) => parseInt((str || '0').replace(/\D/g, ''), 10);
    const formatCpf = (v) => (v || '').replace(/\D/g, '').slice(0, 11).replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    const formatPhone = (v) => {
        v = (v || '').replace(/\D/g, '');
        if (v.length === 11) return v.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
        return v.replace(/^(\d{2})(\d{4})(\d{4})$/, '($1) $2-$3');
    };
    const showErrorPopup = (message) => {
        clearTimeout(errorPopupTimeout);
        errorMessageSpan.textContent = message;
        errorPopup.classList.add('show');
        errorPopupTimeout = setTimeout(() => errorPopup.classList.remove('show'), 5000);
    };
    const updateWithdrawalOptions = () => {
        const balanceType = balanceHiddenInput.value;
        if (balanceType === 'commission') {
            commissionActionsSection.style.display = 'flex';
            const actionType = commissionActionHiddenInput.value;
            if (actionType === 'main_balance') {
                pixSection.classList.add('hide');
                rolloverWarning.classList.remove('hide');
                document.querySelector('.wd-fees-info').classList.add('hide');
            } else {
                pixSection.classList.remove('hide');
                rolloverWarning.classList.add('hide');
                document.querySelector('.wd-fees-info').classList.remove('hide');
            }
        } else {
            commissionActionsSection.style.display = 'none';
            pixSection.classList.remove('hide');
            rolloverWarning.classList.add('hide');
            document.querySelector('.wd-fees-info').classList.remove('hide');
        }
    };
    balanceTrigger.addEventListener('click', (e) => {
        e.stopPropagation();
        balanceDropdown.classList.toggle('open');
    });
    balanceDropdown.querySelectorAll('.dropdown-option').forEach(option => {
        option.addEventListener('click', (e) => {
            e.stopPropagation();
            balanceHiddenInput.value = option.dataset.value;
            balanceTrigger.querySelector('span').textContent = option.querySelector('span').textContent;
            balanceDropdown.classList.remove('open');
            const selectedBalance = getCurrentSelectedBalance();
            currentBalanceDisplay.textContent = formatBRL(selectedBalance);
            updateMinMaxMessage();
            updateWithdrawalOptions();
        });
    });
    commissionActionTrigger.addEventListener('click', (e) => {
        e.stopPropagation();
        commissionActionDropdown.classList.toggle('open');
    });
    commissionActionDropdown.querySelectorAll('.dropdown-option').forEach(option => {
        option.addEventListener('click', (e) => {
            e.stopPropagation();
            commissionActionHiddenInput.value = option.dataset.value;
            commissionActionTrigger.querySelector('span').textContent = option.querySelector('span').textContent;
            commissionActionDropdown.classList.remove('open');
            updateWithdrawalOptions();
        });
    });
    const getCurrentSelectedBalance = () => balanceHiddenInput.value === 'main' ? USER_MAIN_BALANCE_CENTS : USER_COMMISSION_BALANCE_CENTS;
    const fetchUserData = async () => {
        loadingDataMessage.style.display = 'block';
        form.style.display = 'none';
        try {
            const response = await fetch('/api/get_user_withdraw_data.php');
            const data = await response.json();
            if (data.success) {
                USER_DOCUMENT_RAW = data.document_raw;
                USER_EMAIL = data.email;
                USER_PHONE_RAW = data.phone_raw;
                USER_MAIN_BALANCE_CENTS = data.main_balance_cents;
                USER_COMMISSION_BALANCE_CENTS = data.commission_balance_cents;
                cpfInput.value = formatCpf(USER_DOCUMENT_RAW);
                currentBalanceDisplay.textContent = formatBRL(USER_MAIN_BALANCE_CENTS);
                setDefaultPixKey();
                updateMinMaxMessage();
                updateWithdrawalOptions();
            } else {
                showErrorPopup(data.message || 'Erro ao carregar dados.');
                form.querySelector('button[type="submit"]').disabled = true;
            }
        } catch (error) {
            showErrorPopup('Erro de rede. Verifique sua conexão.');
            form.querySelector('button[type="submit"]').disabled = true;
        } finally {
            loadingDataMessage.style.display = 'none';
            form.style.display = 'flex';
        }
    };
    const setDefaultPixKey = () => {
        let type = 'random', value = '', label = 'Chave aleatória';
        if (USER_PHONE_RAW) { type = 'phone'; value = formatPhone(USER_PHONE_RAW); label = 'Telefone'; }
        else if (USER_EMAIL) { type = 'email'; value = USER_EMAIL; label = 'Email'; }
        else if (USER_DOCUMENT_RAW) { type = 'cpf'; value = formatCpf(USER_DOCUMENT_RAW); label = 'CPF'; }
        pixKeyInput.value = value;
        pixHidden.value = type;
        pixLabel.textContent = label;
    };
    const updateMinMaxMessage = () => {
        const currentAmount = moneyToCents(amountInput.value);
        const availableBalance = getCurrentSelectedBalance();
        minMsg.classList.add('hide');
        if (currentAmount > availableBalance) {
            minMsg.textContent = `Saldo insuficiente. Disponível: ${formatBRL(availableBalance)}`;
            minMsg.classList.remove('hide');
        } else if (currentAmount > 0 && currentAmount < MIN_WITHDRAW_VALUE_CENTS) {
            minMsg.textContent = `O valor mínimo é ${formatBRL(MIN_WITHDRAW_VALUE_CENTS)}`;
            minMsg.classList.remove('hide');
        } else if (currentAmount > MAX_WITHDRAW_VALUE_CENTS) {
            minMsg.textContent = `O valor máximo é ${formatBRL(MAX_WITHDRAW_VALUE_CENTS)}`;
            minMsg.classList.remove('hide');
        }
    };
    amountInput.addEventListener('input', (e) => {
        e.target.value = digitsToMoney(e.target.value);
        updateMinMaxMessage();
    });
    chips.forEach(c => c.addEventListener('click', (e) => {
        e.stopPropagation();
        amountInput.value = digitsToMoney((parseInt(c.dataset.v, 10) * 100).toString());
        updateMinMaxMessage();
    }));
    pixTrigger.addEventListener('click', (e) => { e.stopPropagation(); pixMenu.classList.toggle('show'); });
    pixMenu.addEventListener('click', (e) => {
        const btn = e.target.closest('button[data-type]');
        if (!btn) return;
        e.stopPropagation();
        pixHidden.value = btn.dataset.type;
        pixLabel.textContent = btn.textContent.trim();
        pixMenu.classList.remove('show');
        // AQUI ESTÁ A MUDANÇA: Chame a função para definir a chave PIX,
        // mas permita que o usuário a sobrescreva.
        // O setDefaultPixKey() original foi ajustado para isso.

        // NOVO: Adicionar esta função que define a chave com base no tipo selecionado.
        updatePixKeyField(btn.dataset.type);

    });

    const updatePixKeyField = (type) => {
        let value = '';
        switch(type) {
            case 'phone':
                value = USER_PHONE_RAW ? formatPhone(USER_PHONE_RAW) : '';
                break;
            case 'email':
                value = USER_EMAIL || '';
                break;
            case 'cpf':
                value = USER_DOCUMENT_RAW ? formatCpf(USER_DOCUMENT_RAW) : '';
                break;
            case 'random':
                value = ''; // Chave aleatória não tem valor padrão
                break;
        }
        pixKeyInput.value = value;
    };


    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const balanceType = balanceHiddenInput.value;
        const actionType = commissionActionHiddenInput.value;
        const rawAmountCents = moneyToCents(amountInput.value);
        const pixKeyType = pixHidden.value;
        const pixKey = pixKeyInput.value.trim();
        const availableBalance = getCurrentSelectedBalance();

        if (rawAmountCents > availableBalance) {
            return showErrorPopup('Saldo insuficiente.');
        }

        // Validação para saque via PIX
        const isPixWithdrawal = (balanceType === 'main' || (balanceType === 'commission' && actionType === 'pix'));
        if (isPixWithdrawal) {
            if (rawAmountCents < MIN_WITHDRAW_VALUE_CENTS) {
                return showErrorPopup(`O valor mínimo para saque é ${formatBRL(MIN_WITHDRAW_VALUE_CENTS)}.`);
            }
            if (!pixKey) {
                return showErrorPopup('Por favor, preencha a chave PIX.');
            }
        }

        if (rawAmountCents > MAX_WITHDRAW_VALUE_CENTS) {
            return showErrorPopup(`O valor máximo por saque é ${formatBRL(MAX_WITHDRAW_VALUE_CENTS)}.`);
        }

        const submitBtn = form.querySelector('.wd-submit');
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Processando...';

        try {
            const isTransfer = (balanceType === 'commission' && actionType === 'main_balance');
            // CORREÇÃO: Endpoint simplificado, já que era o mesmo nos dois casos.
            const endpoint = '/api/process_withdraw.php';

            const bodyData = {
                amount: rawAmountCents / 100,
                balance_type: balanceType,
                transfer_to_main: isTransfer,
            };

            if (!isTransfer) {
                bodyData.pix_key_type = pixKeyType;
                bodyData.pix_key = pixKey;
                bodyData.document = USER_DOCUMENT_RAW;
            }

            const response = await fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(bodyData)
            });

            // CORREÇÃO 1: Verificar se a resposta HTTP indica um erro (status 4xx ou 5xx).
            if (!response.ok) {
                let errorMessage = 'Ocorreu um erro no servidor.';
                try {
                    // Tenta ler a mensagem de erro que a API enviou no corpo da resposta
                    const errorResult = await response.json();
                    if (errorResult && errorResult.message) {
                        errorMessage = errorResult.message;
                    }
                } catch (jsonError) {
                    // Se o corpo do erro não for JSON, usa o status text como fallback
                    errorMessage = `Erro: ${response.statusText}`;
                }
                // Lança um erro para ser pego pelo bloco catch com a mensagem correta.
                throw new Error(errorMessage);
            }

            const result = await response.json();

            if (result.success) {
                // CORREÇÃO 2: Lógica de sucesso mais robusta e mensagens personalizadas.
                const finalAmount = result.amount ?? (rawAmountCents / 100);
                successAmountSpan.textContent = finalAmount.toFixed(2).replace('.', ',');

                const successTitle = document.getElementById('wd-success-title');
                const successMessage = document.getElementById('wd-success-message');

                if(isTransfer) {
                    successTitle.textContent = "Transferência Realizada!";
                    successMessage.innerHTML = `O valor de R$ <span id="wd-final-amount">${finalAmount.toFixed(2).replace('.', ',')}</span> foi transferido para seu Saldo Principal com sucesso!`;
                } else {
                    successTitle.textContent = "Saque Solicitado!";
                    successMessage.innerHTML = `Seu saque de R$ <span id="wd-final-amount">${finalAmount.toFixed(2).replace('.', ',')}</span> foi solicitado com sucesso! Ele está em análise e o prazo máximo para aprovação é de até 30 minutos.`;
                }

                successOverlay.classList.add('show');
                setTimeout(() => window.location.reload(), 4000);
            } else {
                // Agora, se success for false, a mensagem da API será exibida corretamente.
                showErrorPopup(result.message || 'Erro ao solicitar saque.');
            }

        } catch (error) {
            // CORREÇÃO 3: O catch agora exibe a mensagem de erro específica que lançamos acima,
            // ou a mensagem de erro de rede, se for o caso.
            showErrorPopup(error.message || 'Ocorreu um erro de rede. Tente novamente.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M22 2H2a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h3v9a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-9h3a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1ZM7 20v-2a2 2 0 0 1 2 2Zm10 0h-2a2 2 0 0 1 2-2Zm0-4a4 4 0 0 0-4 4h-2a4 4 0 0 0-4-4V8h10Zm4-6h-2V7a1 1 0 0 0-1-1H6a1 1 0 0 0-1 1v3H3V4h18Zm-9 5a3 3 0 1 0-3-3 3 3 0 0 0 3 3Zm0-4a1 1 0 1 1-1 1 1 1 0 0 1 1-1Z"/></svg> Solicitar Saque <span class="wd-btn-glow"></span>';
        }
    });
    const open = () => {
        mod.classList.remove('submission-success');
        ov.classList.add('show');
        mod.classList.add('show');
        balanceHiddenInput.value = 'main';
        balanceTrigger.querySelector('span').textContent = 'Saldo Principal';
        commissionActionHiddenInput.value = 'pix';
        commissionActionTrigger.querySelector('span').textContent = 'Sacar para PIX';
        fetchUserData();
        amountInput.value = digitsToMoney('000');
    };
    const close = () => {
        ov.classList.remove('show');
        mod.classList.remove('show');
    };
    ov.addEventListener('click', close);
    closeBtn.addEventListener('click', close);

    document.addEventListener('click', (event) => {
        if (!pixMenu.contains(event.target) && event.target !== pixTrigger) {
            pixMenu.classList.remove('show');
        }
        if (!balanceDropdown.contains(event.target)) {
            balanceDropdown.classList.remove('open');
        }
        if (!commissionActionDropdown.contains(event.target)) {
            commissionActionDropdown.classList.remove('open');
        }
    });

    window.openWithdrawModal = open;
})();
</script>