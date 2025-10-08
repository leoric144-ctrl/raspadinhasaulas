<?php
// main-content-transacoes.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    echo "Acesso não autorizado.";
    return;
}

date_default_timezone_set('America/Sao_Paulo');
require_once __DIR__ . '/db.php';

$user_id = $_SESSION['user_id'];
$transactions = [];
$total_pages = 1;
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$type = $_GET['type'] ?? 'deposits'; // 'deposits' ou 'withdrawals'

try {
    $pdo = db(); // Obtém o objeto PDO

    // --- Montagem da Query ---
    $from_clause = " FROM transactions t";
    $base_where_clause = " WHERE t.user_id = ?";
    $params = [$user_id];

    // Condição para o tipo de transação
    if ($type === 'deposits') {
        $base_where_clause .= " AND t.type = 'deposit'";
    } elseif ($type === 'withdrawals') {
        $from_clause .= " LEFT JOIN withdrawals w ON t.withdrawal_id = w.id";
        $base_where_clause .= " AND (t.type = 'WITHDRAWAL' OR t.type = 'COMMISSION_WITHDRAWAL_PIX' OR t.type = 'COMMISSION_TO_MAIN_TRANSFER')";
    }

    $count_query_sql = "SELECT COUNT(*)" . $from_clause . $base_where_clause;
    $stmt_count = $pdo->prepare($count_query_sql);
    $stmt_count->execute($params);
    $total_records = $stmt_count->fetchColumn();

    if ($total_records > 0) {
        $total_pages = ceil($total_records / $limit);
    }

    $select_columns = "SELECT
        t.id,
        t.amount,
        t.status,
        t.created_at,
        t.provider_transaction_id,
        t.pix_code,
        t.type as transaction_type,
        t.description";

    if ($type === 'withdrawals') {
        $select_columns .= ",
        w.rejection_reason,
        w.pix_key_type as withdrawal_pix_key_type,
        w.pix_key as withdrawal_pix_key";
    }

    $order_and_limit_clause = " ORDER BY t.created_at DESC LIMIT ? OFFSET ?";
    $final_query_sql = $select_columns . $from_clause . $base_where_clause . $order_and_limit_clause;

    $stmt_trans = $pdo->prepare($final_query_sql);
    $stmt_trans->bindValue(1, $user_id, PDO::PARAM_INT);
    $stmt_trans->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt_trans->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt_trans->execute();
    $transactions = $stmt_trans->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log("Erro ao carregar as transações do cliente: " . $e->getMessage());
    $transactions = [];
}
?>

<style>
/* Estilos da página de transações */
.transactions-content-wrapper{padding:1rem}.content-header{display:flex;flex-direction:column;align-items:center;gap:1.5rem;text-align:center}.content-header h1{font-size:1.5rem;color:#f0f0f0}.filter-buttons{display:flex;background-color:#1F1F1F;border-radius:8px;padding:.25rem;width:100%}.filter-buttons a{flex-grow:1;text-align:center;padding:.75rem .5rem;border:none;background:transparent;color:#a0a0a0;border-radius:6px;font-weight:600;text-decoration:none;transition:background-color .2s,color .2s}.filter-buttons a.is-active{background:#3F3F46;color:#f0f0f0}.table-wrapper{overflow-x:auto}.transactions-table{width:100%;border-collapse:collapse;color:#f0f0f0}.transactions-table th,.transactions-table td{padding:.75rem .5rem;text-align:left;border-bottom:1px solid #27272a;white-space:nowrap;font-size:.85rem}.transactions-table th{font-size:.7rem;color:#a0a0a0;text-transform:uppercase;font-weight:500}.transactions-table td:first-child{font-weight:600;color:#f0f0f0}.status-pill{display:inline-block;padding:.25rem .75rem;border-radius:99px;font-size:.8rem;font-weight:600}.status-pill.pending{background-color:rgba(245,158,11,.1);color:#f59e0b}.status-pill.paid,.status-pill.approved{background-color:rgba(16,185,129,.1);color:#10b981}.status-pill.expired{background-color:rgba(235,6,12,.08);color:rgba(235,6,12,.72)}.status-pill.rejected{background-color:rgba(235,6,12,.08);color:rgba(235,6,12,.72)}.pagination-controls{display:flex;flex-direction:column;align-items:center;gap:1rem;margin-top:10.5rem;color:#a0a0a0}.pagination-buttons{display:grid;grid-template-columns:1fr 1fr;width:100%;gap:.75rem; flex-wrap:wrap; justify-content:center;padding:0 25px;}.pagination-buttons span.current-page{grid-column:1 / -1;order:-1;margin-bottom:.5rem;text-align:center}.pagination-buttons a,.pagination-buttons span{padding:.75rem;border:1px solid #27272a;border-radius:8px;font-weight:500;text-decoration:none;color:#a0a0a0;background-color:#1F1F1F;text-align:center}.pagination-buttons span.current-page{background:#28e504;color:#000;border-color:#28e504}.pagination-buttons a.disabled{pointer-events:none;opacity:.5}.actions-cell{position:relative}.actions-toggle-btn{background:none;border:none;color:#a0a0a0;cursor:pointer;padding:.5rem;border-radius:50%}.actions-toggle-btn:hover{background-color:#27272a}.actions-dropdown{position:absolute;right:.5rem;top:calc(100% - .5rem);background-color:#2F2F33;border:1px solid #27272a;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,.4);padding:.5rem;z-index:10;opacity:0;visibility:hidden;transform:translateY(-10px);transition:opacity .2s,transform .2s;min-width:180px}.actions-dropdown.show{opacity:1;visibility:visible;transform:translateY(0)}.actions-dropdown button{display:flex;align-items:center;gap:.75rem;background:none;border:none;color:#f0f0f0;padding:.6rem .75rem;border-radius:6px;cursor:pointer;width:100%;text-align:left;font-size:.875rem}.actions-dropdown button:hover{background-color:#3F3F46}.actions-dropdown button:disabled{opacity:.5;cursor:not-allowed}
/* --- NOVO ESTILO ADICIONADO AQUI --- */
.rejection-reason-card {
    background-color: rgba(245, 158, 11, 0.1); /* Fundo amarelo claro */
    color: #eab308; /* Texto em tom de amarelo */
    border: 1px solid rgba(245, 158, 11, 0.2);
    border-radius: 6px; /* Bordas arredondadas */
    padding: 0.6rem;
    margin-top: 0.75rem;
    font-size: 0.8rem;
    white-space: normal; /* Permite a quebra de linha */
    overflow-wrap: break-word; /* Força a quebra de palavras muito longas */
    line-height: 1.4; /* Melhora a legibilidade do texto */
    max-width: 250px; /* Define uma largura máxima para o card */
}
@media (min-width:480px){.transactions-table th,.transactions-table td{padding:.8rem .6rem;font-size:.875rem}.pagination-buttons{display:flex;width:auto;gap:.5rem}.pagination-buttons span.current-page{order:0;margin-bottom:0}.pagination-buttons a,.pagination-buttons span{padding:.5rem 1rem}}@media (min-width:768px){.transactions-content-wrapper{padding:1.5rem}.content-header{flex-direction:row;justify-content:space-between;text-align:left}.filter-buttons{width:auto}.transactions-table th,.transactions-table td{padding:1rem;font-size:.9rem}.transactions-table th{font-size:.8rem}.pagination-controls{flex-direction:row;justify-content:space-between}}@media (min-width:1024px){.transactions-content-wrapper{padding:1rem}.content-header h1{font-size:1.75rem}.transactions-table th,.transactions-table td{padding:1.25rem 1rem;font-size:1rem}.transactions-table th{font-size:.8rem}}
/* Estilos para o novo modal de pagamento */
.pay-modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.8);opacity:0;visibility:hidden;transition:.25s;z-index:9998}.pay-modal-overlay.show{opacity:1;visibility:visible}.pay-modal{position:fixed;top:50%;left:50%;transform:translate(-50%,-50%) scale(.95);background:#18181b;border:1px solid #27272a;border-radius:12px;width:75%;max-width:420px;z-index:9999;opacity:0;visibility:hidden;transition:.25s;color:#fff;text-align:center;padding:2rem}.pay-modal.show{opacity:1;visibility:visible;transform:translate(-50%,-50%) scale(1)}.pay-modal-close{position:absolute;top:14px;right:16px;background:0 0;border:0;color:#ccc;cursor:pointer;opacity:.7;transition:.15s;padding:0}.pay-modal h2{margin:0 0 .5rem;font-size:1.5rem;color:var(--primary-color,#28e504)}.pay-modal p{color:#a0a0a0;margin:0 auto 1.5rem;max-width:35ch}.pay-modal-input{width:100%;background:#27272a;border:1px solid #3f3f46;border-radius:8px;color:#a0a0a0;padding:.8rem;font-size:.9rem;text-overflow:ellipsis;text-align:center;margin-bottom:1rem}.pay-modal-copy-btn{width:100%;background:var(--primary-color,#28e504);color:#000;border:none;border-radius:8px;padding:.8rem;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.5rem;margin-bottom:1.5rem}.pay-modal-timer{font-size:.9rem;color:#a0a0a0}.pay-modal-timer strong{color:#f59e0b;font-size:1rem}.payment-success-view{display:none;padding:2rem 0}.payment-success-view .checkmark{width:80px;height:80px;border-radius:50%;display:block;stroke-width:3;stroke:#fff;stroke-miterlimit:10;margin:0 auto 1rem;box-shadow:inset 0 0 0 var(--primary-color,#28e504);animation:fill-success .4s ease-in-out .4s forwards,scale .3s ease-in-out .9s both}.payment-success-view .checkmark-circle{stroke-dasharray:166;stroke-dashoffset:166;stroke-width:2;stroke-miterlimit:10;stroke:var(--primary-color,#28e504);fill:none;animation:stroke .6s cubic-bezier(.65,0,.45,1) forwards}.payment-success-view .checkmark-check{transform-origin:50% 50%;stroke-dasharray:48;stroke-dashoffset:48;animation:stroke .3s cubic-bezier(.65,0,.45,1) .8s forwards}@keyframes stroke{100%{stroke-dashoffset:0}}@keyframes scale{0%,100%{transform:none}50%{transform:scale3d(1.1,1.1,1)}}@keyframes fill-success{100%{box-shadow:inset 0 0 0 40px var(--primary-color,#28e504)}}
</style>

<div class="transactions-content-wrapper">
    <div class="content-header">
        <h1>Transações</h1>
        <div class="filter-buttons">
            <a href="?view=transacoes&type=deposits" class="<?= $type === 'deposits' ? 'is-active' : '' ?>">Depósitos</a>
            <a href="?view=transacoes&type=withdrawals" class="<?= $type === 'withdrawals' ? 'is-active' : '' ?>">Saques</a>
        </div>
    </div>
    <div class="table-wrapper">
        <table class="transactions-table">
            <thead>
                <tr><th>Valor</th><th>Status</th><th>Data/Hora</th><th>ID Transação</th><th>Ações</th></tr>
            </thead>
            <tbody>
                <?php if (empty($transactions)): ?>
                    <tr><td colspan="5" style="text-align:center; padding: 2rem;">Nenhuma transação encontrada.</td></tr>
                <?php else: ?>
                    <?php foreach ($transactions as $tx): ?>
                        <?php
                        $status = strtolower($tx['status']);
                        $transaction_type = strtolower($tx['transaction_type']);
                        $status_class = '';
                        $status_text = '';
                        $can_pay_pix = false;
                        $time_left_seconds = 0;
                        $is_withdrawal = ($type === 'withdrawals');

                        $created_at = new DateTime($tx['created_at'], new DateTimeZone('UTC'));
                        $created_at->setTimezone(new DateTimeZone('America/Sao_Paulo'));

                        // Lógica para determinar o status e o texto
                        if ($type === 'deposits') {
                            if ($status === 'pending') {
                                $now = new DateTime("now", new DateTimeZone('America/Sao_Paulo'));
                                $expires_at = (clone $created_at)->add(new DateInterval('PT10M'));
                                if ($now >= $expires_at) {
                                    $status_class = 'expired';
                                    $status_text = 'Expirado';
                                } else {
                                    $status_class = 'pending';
                                    $status_text = 'Pendente';
                                    $can_pay_pix = !empty($tx['pix_code']);
                                    $time_left_seconds = $expires_at->getTimestamp() - $now->getTimestamp();
                                }
                            } else {
                                switch ($status) {
                                    case 'paid':
                                    case 'approved':
                                        $status_class = 'paid';
                                        $status_text = 'Pago';
                                        break;
                                    case 'rejected':
                                        $status_class = 'rejected';
                                        $status_text = 'Rejeitado';
                                        break;
                                    default:
                                        $status_class = '';
                                        $status_text = ucfirst($status);
                                        break;
                                }
                            }
                        } else { // type === 'withdrawals'
                            // Lógica de status para saques (incluindo transferências de comissão)
                            if ($transaction_type === 'commission_to_main_transfer') {
                                $status_class = 'paid';
                                $status_text = 'Transferido';
                            } else { // WITHDRAWAL ou COMMISSION_WITHDRAWAL_PIX
                                switch ($status) {
                                    case 'pending': $status_class = 'pending'; $status_text = 'Pendente'; break;
                                    case 'approved': $status_class = 'approved'; $status_text = 'Aprovado'; break;
                                    case 'rejected': $status_class = 'rejected'; $status_text = 'Rejeitado'; break;
                                    default: $status_class = ''; $status_text = ucfirst($status); break;
                                }
                            }
                        }
                        ?>
                        <tr>
                            <td>R$ <?= number_format((float)abs($tx['amount']), 2, ',', '.') ?></td>
                            <td>
                                <span class="status-pill <?= $status_class ?>"><?= htmlspecialchars($status_text) ?></span>

                                <?php if ($is_withdrawal): ?>
                                    <?php if ($status === 'rejected' && !empty($tx['rejection_reason'])): ?>
                                        <div class="rejection-reason-card">
                                            <?= htmlspecialchars($tx['rejection_reason']) ?>
                                        </div>
                                    <?php endif; ?>

                                    <p style="font-size: 0.75rem; color: #a0a0a0; margin-top: 5px;">
                                        Origem:
                                        <?php
                                        // Usa o tipo de transação para determinar a origem do saldo
                                        if ($transaction_type === 'commission_to_main_transfer' || $transaction_type === 'commission_withdrawal_pix') {
                                            echo 'Saldo de Comissão';
                                        } else {
                                            echo 'Saldo Principal';
                                        }
                                        ?>
                                    </p>
                                    <p style="font-size: 0.75rem; color: #a0a0a0; margin-top: 2px;">
                                        Destino:
                                        <?php
                                        if ($transaction_type === 'commission_to_main_transfer') {
                                            echo 'Saldo Principal';
                                        } elseif (!empty($tx['withdrawal_pix_key'])) {
                                            echo 'PIX ' . htmlspecialchars(ucfirst(str_replace('_', ' ', $tx['withdrawal_pix_key_type']))) . ' - ' . htmlspecialchars($tx['withdrawal_pix_key']);
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </p>
                                <?php endif; ?>
                            </td>
                            <td><?= $created_at->format('d/m/Y, H:i:s') ?></td>
                            <td><?= htmlspecialchars($tx['provider_transaction_id'] ?? 'N/A') ?></td>
                            <td class="actions-cell">
                                <button class="actions-toggle-btn" aria-label="Abrir ações">
                                    <svg fill="currentColor" width="20" height="20" viewBox="0 0 24 24"><path d="M12 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0-6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 12c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"></path></svg>
                                </button>
                                <div class="actions-dropdown">
                                    <?php if ($type === 'deposits' && $can_pay_pix): ?>
                                        <button class="pay-pix-btn"
                                                data-pixcode="<?= htmlspecialchars($tx['pix_code']) ?>"
                                                data-txid="<?= htmlspecialchars($tx['provider_transaction_id']) ?>"
                                                data-timeleft="<?= $time_left_seconds ?>"
                                                data-amount="<?= number_format((float)$tx['amount'], 2, ',', '.') ?>">
                                            <svg fill="currentColor" width="16" height="16" viewBox="0 0 24 24"><path d="M19,21H8V7H19M19,5H8A2,2 0 0,0 6,7V21A2,2 0 0,0 8,23H19A2,2 0 0,0 21,21V7A2,2 0 0,0 19,5M16,1H4A2,2 0 0,0 2,3V17H4V3H16V1Z" /></svg>
                                            Pagar Código
                                        </button>
                                    <?php endif; ?>
                                    <button class="copy-id-btn" data-id="<?= htmlspecialchars($tx['provider_transaction_id'] ?? 'N/A') ?>">
                                        <svg fill="currentColor" width="16" height="16" viewBox="0 0 16 16"><path d="M13 0H6a2 2 0 0 0-2 2 2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h7a2 2 0 0 0 2-2 2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zM4 4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1zm10 2a1 1 0 0 1-1 1H6V5h8a1 1 0 0 1 1 1z"/></svg>
                                        Copiar ID
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination-controls">
        <div class="records-per-page">
            <span>Mostrar 10 registros por página</span>
        </div>
        <div class="pagination-buttons">
            <a href="?view=transacoes&type=<?= $type ?>&page=<?= $page - 1; ?>" class="<?= ($page <= 1) ? 'disabled' : '' ?>">Anterior</a>
            <span class="current-page"><?= $page; ?></span>
            <a href="?view=transacoes&type=<?= $type ?>&page=<?= $page + 1; ?>" class="<?= ($page >= $total_pages) ? 'disabled' : '' ?>">Próximo</a>
        </div>
    </div>
</div>

<div id="pay-modal-overlay" class="pay-modal-overlay"></div>
<div id="pay-modal" class="pay-modal" role="dialog" aria-hidden="true">
    <button type="button" id="pay-modal-close" class="pay-modal-close" aria-label="Fechar">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" stroke="currentColor" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
    </button>
    <div id="pay-modal-content">
        <h2>Pagar PIX - R$ <span id="pay-modal-amount"></span></h2>
        <p>Copie o código abaixo e pague no app do seu banco. Estamos verificando o pagamento automaticamente.</p>
        <p>Mantenha esse pop-up aberto para que possamos confirmar o pagamento automaticamente</p>
        <input type="text" id="pay-modal-input" readonly>
        <button id="pay-modal-copy-btn" class="pay-modal-copy-btn">
            <svg fill="currentColor" width="16" height="16" viewBox="0 0 16 16"><path d="M13 0H6a2 2 0 0 0-2 2 2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h7a2 2 0 0 0 2-2 2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zM4 4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1zm10 2a1 1 0 0 1-1 1H6V5h8a1 1 0 0 1 1 1z"/></svg>
            <span id="pay-modal-copy-btn-text">Copiar Código</span>
        </button>
        <div class="pay-modal-timer">
            Expira em: <strong id="pay-modal-timer-display">10:00</strong>
        </div>
    </div>
    <div id="payment-success-view" class="payment-success-view">
        <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52"><circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/><path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/></svg>
        <h2>Pagamento Aprovado!</h2>
        <p>Seu saldo será atualizado em breve.</p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Dropdown e copiar ID
    const closeAllDropdowns = () => document.querySelectorAll('.actions-dropdown.show').forEach(d => d.classList.remove('show'));
    document.querySelectorAll('.actions-toggle-btn').forEach(toggle => {
        toggle.addEventListener('click', e => {
            e.stopPropagation();
            const dropdown = toggle.nextElementSibling;
            const isShowing = dropdown.classList.contains('show');
            closeAllDropdowns();
            if (!isShowing) dropdown.classList.add('show');
        });
    });
    document.querySelectorAll('.copy-id-btn').forEach(button => button.addEventListener('click', function() {
        navigator.clipboard.writeText(this.dataset.id);
        const originalText = this.innerHTML;
        this.innerHTML = 'Copiado!';
        setTimeout(() => { this.innerHTML = originalText; }, 2000);
    }));
    window.addEventListener('click', closeAllDropdowns);

    // Modal de pagamento
    const payModalOverlay = document.getElementById('pay-modal-overlay');
    const payModal = document.getElementById('pay-modal');
    const payModalClose = document.getElementById('pay-modal-close');
    const payButtons = document.querySelectorAll('.pay-pix-btn');

    let timerInterval = null;
    let pollingInterval = null;

    const openPayModal = (dataset) => {
        document.getElementById('pay-modal-amount').textContent = dataset.amount;
        document.getElementById('pay-modal-input').value = dataset.pixcode;

        payModalOverlay.classList.add('show');
        payModal.classList.add('show');

        document.getElementById('pay-modal-content').style.display = 'block';
        document.getElementById('payment-success-view').style.display = 'none';

        startTimer(parseInt(dataset.timeleft, 10));
        startPolling(dataset.txid);
    };

    const closePayModal = () => {
        payModalOverlay.classList.remove('show');
        payModal.classList.remove('show');
        clearInterval(timerInterval);
        clearInterval(pollingInterval);
    };

    const startTimer = (timeLeft) => {
        clearInterval(timerInterval);
        const timerDisplay = document.getElementById('pay-modal-timer-display');

        const updateTimer = () => {
            if (timeLeft < 0) {
                clearInterval(timerInterval);
                timerDisplay.textContent = "Expirado";
                setTimeout(() => window.location.reload(), 2000);
                return;
            }
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timerDisplay.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            timeLeft--;
        };
        updateTimer();
        timerInterval = setInterval(updateTimer, 1000);
    };

    const startPolling = (transactionId) => {
        clearInterval(pollingInterval);
        pollingInterval = setInterval(async () => {
            try {
                const response = await fetch(`/api/verificar-pix.php?id=${transactionId}`);
                if (!response.ok) return;
                const data = await response.json();
                if (data.status === 'PAID') {
                    showSuccessScreen();
                }
            } catch (error) {
                console.error("Erro ao verificar pagamento:", error);
            }
        }, 3000);
    };

    const showSuccessScreen = () => {
        clearInterval(timerInterval);
        clearInterval(pollingInterval);

        document.getElementById('pay-modal-content').style.display = 'none';
        document.getElementById('payment-success-view').style.display = 'block';

        setTimeout(() => {
            closePayModal();
            window.location.reload();
        }, 3000);
    };

    payButtons.forEach(button => {
        button.addEventListener('click', () => {
            openPayModal(button.dataset);
        });
    });

    payModalClose.addEventListener('click', closePayModal);
    payModalOverlay.addEventListener('click', closePayModal);

    const copyBtn = document.getElementById('pay-modal-copy-btn');
    const copyBtnText = document.getElementById('pay-modal-copy-btn-text');
    copyBtn.addEventListener('click', () => {
        const input = document.getElementById('pay-modal-input');
        input.select();
        document.execCommand('copy');
        copyBtnText.textContent = 'Copiado!';
        setTimeout(() => { copyBtnText.textContent = 'Copiar Código'; }, 2000);
    });
});
</script>