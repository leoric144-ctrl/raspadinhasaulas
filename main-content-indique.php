<?php
// main-content-indique.php - VERSÃO COM LÓGICA FINAL USANDO commission_transactions

require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('America/Sao_Paulo');

// Inicializa variáveis
$user_referral_code = null;
$user_info = [];
$referral_stats = [
    'balance' => '0,00', 'indications' => 0, 'active_indications' => 0,
    'total_withdrawn' => '0,00', 'total_commission' => '0,00'
];
$error_message = null;
$level_images = [
    1 => 'https://ik.imagekit.io/kyjz2djk3p/bronze-removebg-preview.png?updatedAt=1753997974778',
    2 => 'https://ik.imagekit.io/kyjz2djk3p/prata.png?updatedAt=1753998169234',
    3 => 'https://ik.imagekit.io/kyjz2djk3p/ouro.png?updatedAt=1753998236916',
];
$all_referral_levels_data = [];
$affiliated_deposits_display_data = [];

function updateUserLevel(PDO $pdo, int $userId) {
    $stmtUser = $pdo->prepare("SELECT xp, level_id FROM users WHERE id = ?");
    $stmtUser->execute([$userId]);
    $userData = $stmtUser->fetch(PDO::FETCH_ASSOC);
    if (!$userData) return;
    $currentXp = (int)$userData['xp'];
    $currentLevelId = (int)$userData['level_id'];
    $stmtNextLevel = $pdo->prepare("SELECT id, commission_rate FROM referral_levels WHERE min_xp <= ? ORDER BY min_xp DESC LIMIT 1");
    $stmtNextLevel->execute([$currentXp]);
    $nextLevel = $stmtNextLevel->fetch(PDO::FETCH_ASSOC);
    if ($nextLevel && $currentLevelId != $nextLevel['id']) {
        $stmtUpdate = $pdo->prepare("UPDATE users SET level_id = ?, commission_rate = ? WHERE id = ?");
        $stmtUpdate->execute([$nextLevel['id'], $nextLevel['commission_rate'], $userId]);
    }
}

try {
    $pdo = db();
    if (!isset($_SESSION['user_id'])) {
        header('Location: /index.php');
        exit;
    }
    $user_id = (int)$_SESSION['user_id'];

    updateUserLevel($pdo, $user_id);

    $stmt = $pdo->prepare(
        "SELECT u.name, u.email, u.referral_code, u.commission_balance, u.total_commission_earned,
               u.total_commission_withdrawn, u.commission_rate, u.xp, u.level_id,
               rl.name AS level_name, rl.min_xp AS level_min_xp
        FROM users u
        JOIN referral_levels rl ON u.level_id = rl.id
        WHERE u.id = ?"
    );
    $stmt->execute([$user_id]);
    $db_user_data = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt_all_levels_modal = $pdo->prepare("SELECT id, name, min_xp, commission_rate FROM referral_levels ORDER BY min_xp ASC");
    $stmt_all_levels_modal->execute();
    $all_referral_levels_data = $stmt_all_levels_modal->fetchAll(PDO::FETCH_ASSOC);

    if ($db_user_data) {
        $user_referral_code = $db_user_data['referral_code'];

        $stmt_total_indications_query = $pdo->prepare("SELECT COUNT(id) FROM users WHERE referrer_id = ?");
        $stmt_total_indications_query->execute([$user_id]);
        $total_indications_count = $stmt_total_indications_query->fetchColumn();

        // ##### CORREÇÃO 1: Contagem de ativas agora usa a tabela commission_transactions #####
        $stmt_active_indications_query = $pdo->prepare(
            "SELECT COUNT(DISTINCT referred_user_id) FROM commission_transactions WHERE user_id = ? AND type = 'deposit_commission'"
        );
        $stmt_active_indications_query->execute([$user_id]);
        $active_indications_count = $stmt_active_indications_query->fetchColumn();

        // Lógica de XP (sem alterações)
        // ... (o bloco de XP continua o mesmo)
        $next_level_name = "Nível Máximo";
        $next_level_min_xp = $db_user_data['level_min_xp'];
        $stmt_next_level = $pdo->prepare("SELECT name, min_xp FROM referral_levels WHERE min_xp > ? ORDER BY min_xp ASC LIMIT 1");
        $stmt_next_level->execute([$db_user_data['level_min_xp']]);
        $next_level_data = $stmt_next_level->fetch(PDO::FETCH_ASSOC);
        if ($next_level_data) {
            $next_level_name = htmlspecialchars($next_level_data['name']);
            $next_level_min_xp = $next_level_data['min_xp'];
        }
        $xp_current = (int)$db_user_data['xp'];
        $xp_percentage = 0;
        if ($next_level_data) {
            $xp_base_current_level = (int)$db_user_data['level_min_xp'];
            $xp_total_between_levels = $next_level_min_xp - $xp_base_current_level;
            $xp_progress_in_level = $xp_current - $xp_base_current_level;
            $xp_percentage = ($xp_total_between_levels > 0) ? ($xp_progress_in_level / $xp_total_between_levels) * 100 : 100;
            $xp_text_display = "{$xp_current} / {$next_level_min_xp} XP para o nível {$next_level_name}";
        } else {
            $xp_percentage = 100;
            $xp_text_display = "{$xp_current} XP (Nível Máximo Atingido!)";
        }

        // =========================================================
        // ✅ NOVA LÓGICA: BUSCA E SOMA DOS SAQUES DE COMISSÃO
        // =========================================================
        // Ajusta a consulta para somar corretamente os valores
        $stmt_total_withdrawn = $pdo->prepare(
            "SELECT SUM(CASE
                WHEN type = 'COMMISSION_TO_MAIN_TRANSFER' THEN amount
                WHEN type = 'COMMISSION_WITHDRAWAL_PIX' THEN ABS(amount)
                ELSE 0
            END) AS total_comission_withdrawn
            FROM transactions
            WHERE user_id = ?
            AND type IN ('COMMISSION_TO_MAIN_TRANSFER', 'COMMISSION_WITHDRAWAL_PIX')
            AND status IN ('COMPLETED', 'APPROVED')"
        );
        $stmt_total_withdrawn->execute([$user_id]);
        $withdrawn_data = $stmt_total_withdrawn->fetch(PDO::FETCH_ASSOC);

        // O valor do saque na tabela de transações é negativo, então usamos abs() para obter o valor absoluto
        $total_withdrawn_value = (float)($withdrawn_data['total_comission_withdrawn'] ?? 0);

        $user_info = [
            'name' => $db_user_data['name'], 'email' => $db_user_data['email'], 'level_id' => $db_user_data['level_id'],
            'level_name' => $db_user_data['level_name'], 'level_image_url' => $level_images[$db_user_data['level_id']] ?? '',
            'commission_rate' => number_format((float)$db_user_data['commission_rate'] * 100, 0),
            'xp_current' => $xp_current, 'xp_needed_for_next_level' => $next_level_min_xp,
            'xp_percentage' => min(100, max(0, $xp_percentage)), 'xp_text_display' => $xp_text_display,
            'next_level_name' => $next_level_name
        ];

        $referral_stats = [
            'balance' => number_format((float)$db_user_data['commission_balance'], 2, ',', '.'),
            'indications' => $total_indications_count, 'active_indications' => $active_indications_count,
            'total_withdrawn' => number_format($total_withdrawn_value, 2, ',', '.'),
            'total_commission' => number_format((float)$db_user_data['total_commission_earned'], 2, ',', '.')
        ];

        // ##### CORREÇÃO 2: A listagem de afiliados agora usa a tabela commission_transactions #####
        $stmt_affiliated_deposits = $pdo->prepare(
            "SELECT
                ct.referred_user_id AS affiliated_user_id,
                u.name AS affiliate_name,
                u.email AS affiliate_email,
                ct.amount AS commission_earned,
                ct.description,
                ct.created_at
             FROM commission_transactions ct
             JOIN users u ON ct.referred_user_id = u.id
             WHERE
                ct.user_id = ?
                AND ct.type = 'deposit_commission'
             ORDER BY ct.created_at DESC"
        );
        $stmt_affiliated_deposits->execute([$user_id]);
        $raw_affiliated_transactions = $stmt_affiliated_deposits->fetchAll(PDO::FETCH_ASSOC);

        foreach ($raw_affiliated_transactions as $transaction) {
            $affiliate_id = $transaction['affiliated_user_id'];

            if (!isset($affiliated_deposits_display_data[$affiliate_id])) {
                $affiliated_deposits_display_data[$affiliate_id] = [
                    'name' => htmlspecialchars($transaction['affiliate_name']),
                    'email' => htmlspecialchars(substr($transaction['affiliate_email'], 0, 3) . '***@' . explode('@', $transaction['affiliate_email'])[1]),
                    'total_deposited' => 0,
                    'total_commission_from_this_affiliate' => 0,
                    'deposits' => []
                ];
            }

            $commission_earned = (float)$transaction['commission_earned'];

            // Extrair o valor original do depósito da string de descrição
            $original_deposit_amount = 0;
            if (preg_match('/R\$\s*([\d,.]+)/', $transaction['description'], $matches)) {
                $amount_str = str_replace(['.', ','], ['', '.'], $matches[1]);
                $original_deposit_amount = (float)$amount_str;
            }

            $affiliated_deposits_display_data[$affiliate_id]['total_deposited'] += $original_deposit_amount;
            $affiliated_deposits_display_data[$affiliate_id]['total_commission_from_this_affiliate'] += $commission_earned;

            $deposit_datetime = new DateTime($transaction['created_at']);
            $deposit_datetime->setTimezone(new DateTimeZone('America/Sao_Paulo'));

            $affiliated_deposits_display_data[$affiliate_id]['deposits'][] = [
                'amount' => number_format($original_deposit_amount, 2, ',', '.'),
                'commission_earned' => number_format($commission_earned, 2, ',', '.'),
                'date' => $deposit_datetime->format('d/m/Y H:i')
            ];
        }

    } else {
        throw new Exception("Usuário não encontrado no banco de dados.");
    }
} catch (Exception $e) {
    $error_message = "Erro ao carregar os dados: " . $e->getMessage();
    error_log($error_message);
}

$protocol = "https";
$host = $_SERVER['HTTP_HOST'];
$base_url = "{$protocol}://{$host}/r/";
?>

<style>
/* SEU CSS EXISTENTE */
:root {
    --primary-green: #28e504;
    --background-dark: #111111;
    --card-bg: #1A1A1A;
    --border-color: #27272a;
    --text-primary: #ffffff;
    --text-secondary: #a0a0a0;
}
.referral-page-container {
    max-width: 1400px;
    margin: 1.5rem auto;
    display: flex;
    align-items: flex-start;
    gap: 1.5rem;
}
.referral-sidebar {
    flex: 0 0 300px;
}
.referral-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}
/* Card do Usuário (Sidebar) */
.user-card { background-color: var(--card-bg); border: 1px solid var(--border-color); border-radius: 10px; padding: 1.5rem; color: var(--text-primary); }
.user-info { display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; }
.user-info img.avatar { width: 48px; height: 48px; border-radius: 50%; }
.user-details .email { font-size: 0.9rem; font-weight: 500; }
.level-display { display: flex; gap: 0.5rem; margin-top: 4px; border-radius: 20px; border: 1px solid var(--border-color); width: 120px; height: 27px; justify-content: center; align-items: center; }
.level-display img.level-icon { width: 25px; height: 25px; }
.level-display span { font-size: 0.8rem; color: var(--text-secondary); }
.commission-tag { background-color: #2F2F33; border-radius: 6px; padding: 4px 8px; font-size: 0.8rem; font-weight: 500; margin-bottom: 1.5rem; display: inline-block; }
.xp-bar { background-color: #3A3A3C; border-radius: 99px; height: 8px; width: 100%; margin-bottom: 0.5rem; overflow: hidden; }
.xp-bar-fill { background-color: var(--primary-green); height: 100%; border-radius: 99px; transition: width 0.5s ease-out; }
.xp-text { font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 1.5rem; }
.btn-primary { display: inline-flex; justify-content: center; align-items: center; gap: 0.5rem; width: 100%; padding: .75rem; background-color: var(--primary-green); color: #000; border: none; border-radius: 8px; font-weight: 700; text-align: center; cursor: pointer; transition: opacity .2s; }
.btn-primary:hover { opacity: .9; }

/* Cards do Conteúdo Principal */
.content-card { background-color: var(--card-bg); border: 1px solid var(--border-color); border-radius: 10px; padding: 1.5rem 2rem; }
.balance-card { display: flex; justify-content: space-between; align-items: center; }
.balance-info .label { font-size: 0.9rem; color: var(--text-secondary); }
.balance-info .amount { font-size: 1.75rem; font-weight: 700; color: var(--text-primary); }
.summary-card h3 { margin-top: 0; margin-bottom: 1.5rem; font-size: 1.2rem; }
.summary-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; }

.stat-box { background-color: var(--background-dark); border: 1px solid var(--border-color); border-radius: 8px; display: flex; }
.stat-box-content { display: flex; align-items: center; width: 100%; padding: 1.25rem; gap: 1rem; }
.stat-box .icon { color: #ffffff; width: 48px; height: 48px; border-radius: 12px; background-color: #27272a; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
.stat-box .icon svg { width: 24px; height: 24px; }
.stat-box .value { font-size: 1.25rem; font-weight: 600; margin: 0; line-height: 1.3; }
.stat-box .label { font-size: 0.85rem; color: var(--text-secondary); margin: 0; line-height: 1.3; }

.link-card h3 { margin-top: 0; margin-bottom: 1.5rem; font-size: 1.2rem; }
.link-group { margin-bottom: 1rem; }
.link-group .label { font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.5rem; }
.link-display { display: flex; align-items: center; background-color: var(--background-dark); border: 1px solid var(--border-color); border-radius: 8px; padding: 0.75rem 1rem; }
.link-display span { flex: 1; color: var(--text-primary); font-family: monospace; overflow-wrap: break-word; word-break: break-all; }
.link-display .copy-btn { background: none; border: none; color: var(--text-secondary); cursor: pointer; }
.create-code-container { display: flex; justify-content: space-between; align-items: flex-end; gap: 1rem; }
.create-code-container .link-group { margin-bottom: 0; flex-grow: 1; }
.create-code-container .btn-primary { width: auto; padding: 0.7rem 1.5rem; }
.btn-primary.loading {
    cursor: not-allowed;
    opacity: 0.7;
}
.btn-primary.loading .button-text {
    display: none;
}
.btn-primary.loading .button-spinner {
    display: inline-block;
    width: 1em;
    height: 1em;
    border: 0.15em solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}
.btn-primary .button-spinner {
    display: none;
}
@keyframes spin {
    to { transform: rotate(360deg); }
}
@media (max-width: 992px) {
    .referral-page-container {
        width: 100%;
        max-width: none;
        margin: 0;
        padding: 1.5rem;
        box-sizing: border-box;
        flex-direction: column;
        gap: 1.5rem;
    }
    .referral-sidebar {
        flex-basis: auto;
        width: 100%;
    }
}
@media (max-width: 768px) {
    .referral-page-container {
        padding: 1rem;
        gap: 1rem;
    }
    .summary-grid {
        grid-template-columns: 1fr;
    }
    .balance-card {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
}
@media (max-width: 480px) {
    .referral-page-container {
        padding: 1rem 0.75rem;
    }
    .content-card, .user-card {
        padding: 1.25rem 1rem;
    }
    .create-code-container {
        flex-direction: column;
        align-items: stretch;
    }
    .create-code-container .btn-primary {
        width: 100%;
    }
}
/* ==== Estilos para o Modal de Níveis ==== */
.levels-modal-overlay, .withdraw-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.8);
    z-index: 9998;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s;
    display: flex;
    justify-content: center;
    align-items: center;
}
.levels-modal-overlay.show, .withdraw-modal-overlay.show {
    opacity: 1;
    visibility: visible;
}
.levels-modal, .withdraw-modal {
    background: var(--background-dark);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.55);
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    padding: 2rem;
    position: relative;
    transform: translateY(20px);
    transition: transform 0.3s ease-out, opacity 0.3s ease-out;
}
.levels-modal-overlay.show .levels-modal, .withdraw-modal-overlay.show .withdraw-modal {
    transform: translateY(0);
    opacity: 1;
}
.levels-modal-close, .withdraw-modal-close {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: none;
    border: none;
    color: #777;
    cursor: pointer;
    font-size: 1.5rem;
}
.levels-modal-close:hover, .withdraw-modal-close:hover {
    color: #fff;
}
.levels-modal h2, .withdraw-modal h2 {
    text-align: center;
    color: var(--primary-green);
    margin-bottom: 1.5rem;
    font-size: 1.8rem;
}
.level-card-modal {
    background-color: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 1.2rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}
.level-card-modal .level-icon {
    width: 40px;
    height: 40px;
    flex-shrink: 0;
}
.level-card-modal .level-info h3 {
    margin: 0;
    font-size: 1.3rem;
    color: var(--text-primary);
}
.level-card-modal .level-info p {
    margin: 0.25rem 0 0;
    font-size: 0.9rem;
    color: var(--text-secondary);
}
.level-card-modal .level-info .commission-rate {
    color: var(--primary-green);
    font-weight: 600;
}
.level-card-modal .level-info .requirements {
    font-style: italic;
}
.level-card-modal.current-level {
    border-color: var(--primary-green);
    box-shadow: 0 0 10px rgba(40, 229, 4, 0.3);
}
.xp-bonus-text {
    font-size: 0.85em;
    color: #8E8E93;
    margin-top: 5px;
    line-height: 1.4;
}
.xp-bonus-text .xp-highlight {
    color: #00E880;
    font-weight: 700;
}
@media (max-width: 480px) {
    .levels-modal, .withdraw-modal {
        padding: 1.5rem;
    }
    .levels-modal h2, .withdraw-modal h2 {
        font-size: 1.5rem;
    }
    .level-card-modal {
        flex-direction: column;
        text-align: center;
    }
    .level-card-modal .level-info h3 {
        font-size: 1.1rem;
    }
    .level-card-modal .level-info p {
        font-size: 0.85rem;
    }
}
.affiliates-list-card {
    padding: 1.5rem 2rem;
}
.affiliates-list-card h3 {
    margin-top: 0;
    margin-bottom: 1.5rem;
    font-size: 1.2rem;
    color: var(--text-primary);
}
.affiliates-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1rem;
}
.affiliate-item {
    background-color: var(--background-dark);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
.affiliate-header {
    display: flex;
    flex-direction: column;
    margin-bottom: 0.5rem;
}
.affiliate-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--primary-green);
}
.affiliate-email {
    font-size: 0.85rem;
    color: var(--text-secondary);
}
.affiliate-summary p {
    margin: 0;
    font-size: 0.95rem;
    color: var(--text-primary);
    padding-bottom: 0.5rem;
    border-bottom: 1px dashed var(--border-color);
    margin-bottom: 0.75rem;
}
.affiliate-summary strong {
    color: var(--primary-green);
}
.affiliate-deposits-details h4 {
    margin-top: 0;
    margin-bottom: 0.75rem;
    font-size: 0.9rem;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.deposit-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #222;
    padding: 8px 12px;
    border-radius: 6px;
    margin-bottom: 5px;
}
.deposit-amount {
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--primary-green);
}
.deposit-date {
    font-size: 0.8em;
    color: var(--text-secondary);
}
.no-affiliates-msg,
.no-deposits {
    color: var(--text-secondary);
    font-style: italic;
    text-align: center;
    padding: 1rem;
}
@media (max-width: 768px) {
    .affiliates-grid {
        grid-template-columns: 1fr;
    }
}
.deposit-item > div {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 3px;
}
.deposit-commission {
    font-size: 0.75rem;
    color: #00d492;
    font-weight: 500;
}
</style>

<main class="referral-page-container">

    <aside class="referral-sidebar">
        <?php if (!empty($user_info)): ?>
        <div class="user-card">
            <div class="user-info">
                <img src="https://ik.imagekit.io/kyjz2djk3p/avatar-15.png?updatedAt=1757344931522" alt="Avatar do usuário" class="avatar">
                <div class="user-details">
                    <span class="email"><?php echo htmlspecialchars($user_info['email']); ?></span>
                    <div class="level-display">
                        <img src="<?php echo htmlspecialchars($user_info['level_image_url']); ?>" alt="Nível <?php echo htmlspecialchars($user_info['level_name']); ?>" class="level-icon">
                        <span><?php echo htmlspecialchars($user_info['level_name']); ?></span>
                    </div>
                </div>
            </div>
            <span class="commission-tag">Comissão <?php echo htmlspecialchars($user_info['commission_rate']); ?>%</span>
            <div class="xp-bar">
                <div class="xp-bar-fill" style="width: <?php echo htmlspecialchars($user_info['xp_percentage']); ?>%;"></div>
            </div>
            <p class="xp-text">
                <?php echo htmlspecialchars($user_info['xp_text_display']); ?>
            </p>
            <p class="xp-bonus-text">
                A cada depósito do seu afiliado você ganha <span class="xp-highlight">100</span> de XP
            </p>
            <button class="btn-primary" style="width:100%" id="view-levels-btn">Ver níveis</button> </div>
        <?php endif; ?>
    </aside>

    <section class="referral-content">

        <?php if ($error_message): ?>
            <div class="content-card">
                <p style="color: #ff6b6b;"><?php echo htmlspecialchars($error_message); ?></p>
            </div>
        <?php elseif ($user_referral_code): ?>

            <div class="content-card balance-card">
                <div class="balance-info">
                    <p class="label">Saldo Atual</p>
                    <p class="amount">R$ <?php echo htmlspecialchars($referral_stats['balance']); ?></p>
                </div>
                <button class="btn-primary" style="width: auto; padding: 0.6rem 1.2rem;" id="withdraw-commission-btn">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="15" height="15"><path d="M22 2H2a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h3v9a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-9h3a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1ZM7 20v-2a2 2 0 0 1 2 2Zm10 0h-2a2 2 0 0 1 2-2Zm0-4a4 4 0 0 0-4 4h-2a4 4 0 0 0-4-4V8h10Zm4-6h-2V7a1 1 0 0 0-1-1H6a1 1 0 0 0-1 1v3H3V4h18Zm-9 5a3 3 0 1 0-3-3 3 3 0 0 0 3 3Zm0-4a1 1 0 1 1-1 1 1 1 0 0 1 1-1Z"></path></svg>
                    Retirada
                </button>
            </div>

            <div class="content-card summary-card">
                <h3>Resumo</h3>
                <div class="summary-grid">
                    <div class="stat-box">
                        <div class="stat-box-content">
                            <div class="icon">
                                <svg fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20c0-1.657-2.239-3-5-3s-5 1.343-5 3m14-3c0-1.23-1.234-2.287-3-2.75M3 17c0-1.23 1.234-2.287 3-2.75m12-4.014a3 3 0 1 0-4-4.472m-8 4.472a3 3 0 0 1 4-4.472M12 14a3 3 0 1 1 0-6 3 3 0 0 1 0 6Z"></path></svg>
                            </div>
                            <div>
                                <p class="value"><?php echo htmlspecialchars($referral_stats['indications']); ?></p>
                                <p class="label">Indicações</p>
                            </div>
                        </div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-box-content">
                            <div class="icon">
                                <svg width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24" pathfill="currentColor" xmlns="http://www.w3.org/2000/svg" class="size-10 p-0.5"><path fill="currentColor" d="M17.755 14c.78 0 1.467.397 1.87 1H13.5c-.563 0-1.082.186-1.5.5H6.253a.75.75 0 0 0-.75.75v.577c0 .535.192 1.053.54 1.46C7.138 19.57 8.777 20.303 11 20.467V21.5q0 .243.045.472c-2.677-.169-4.74-1.066-6.143-2.71a3.75 3.75 0 0 1-.898-2.435v-.578A2.25 2.25 0 0 1 6.253 14zM12 2.005a5 5 0 1 1 0 10 5 5 0 0 1 0-10Zm0 1.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7ZM12 17.5a1.5 1.5 0 0 1 1.5-1.5h8a1.5 1.5 0 0 1 1.5 1.5v4a1.5 1.5 0 0 1-1.5 1.5h-8a1.5 1.5 0 0 1-1.5-1.5zm10 .5a1 1 0 0 1-1-1h-1a2 2 0 0 0 2 2zm0 2a2 2 0 0 0-2 2h1a1 1 0 0 1 1-1zm-8-3a1 1 0 0 1-1 1v1a2 2 0 0 0 2-2zm1 5a2 2 0 0 0-2-2v1a1 1 0 0 1 1 1zm4.25-2.5a1.75 1.75 0 1 0-3.5 0 1.75 1.75 0 0 0 3.5 0Z"></path></svg>
                            </div>
                            <div>
                                <p class="value"><?php echo htmlspecialchars($referral_stats['active_indications']); ?></p>
                                <p class="label">Indicações Ativas<br> (Usuários que fizeram pelo menos um Depósito)</p>
                            </div>
                        </div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-box-content">
                            <div class="icon">
                                <svg fill="none" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" fill-rule="evenodd" d="M28.772 24.667A4 4 0 0 0 25 22v-1h-2v1a4 4 0 1 0 0 8v4c-.87 0-1.611-.555-1.887-1.333a1 1 0 1 0-1.885.666A4 4 0 0 0 23 36v1h2v-1a4 4 0 0 0 0-8v-4c.87 0 1.611.555 1.886 1.333a1 1 0 1 0 1.886-.666ZM23 24a2 2 0 1 0 0 4zm2 10a2 2 0 1 0 0-4z" clip-rule="evenodd"></path><path fill="currentColor" fill-rule="evenodd" d="M13.153 8.621C15.607 7.42 19.633 6 24.039 6c4.314 0 8.234 1.361 10.675 2.546l.138.067c.736.364 1.33.708 1.748.987L32.906 15C41.422 23.706 48 41.997 24.039 41.997S6.479 24.038 15.069 15l-3.67-5.4c.283-.185.642-.4 1.07-.628q.318-.171.684-.35Zm17.379 6.307 2.957-4.323c-2.75.198-6.022.844-9.172 1.756-2.25.65-4.75.551-7.065.124a25 25 0 0 1-1.737-.386l1.92 2.827c4.115 1.465 8.981 1.465 13.097.002ZM16.28 16.63c4.815 1.86 10.602 1.86 15.417-.002a29.3 29.3 0 0 1 4.988 7.143c1.352 2.758 2.088 5.515 1.968 7.891-.116 2.293-1.018 4.252-3.078 5.708-2.147 1.517-5.758 2.627-11.537 2.627-5.785 0-9.413-1.091-11.58-2.591-2.075-1.437-2.986-3.37-3.115-5.632-.135-2.35.585-5.093 1.932-7.87 1.285-2.648 3.078-5.197 5.005-7.274Zm-1.15-6.714c.8.238 1.636.445 2.484.602 2.15.396 4.306.454 6.146-.079a54 54 0 0 1 6.53-1.471C28.45 8.414 26.298 8 24.038 8c-3.445 0-6.658.961-8.908 1.916Z" clip-rule="evenodd"></path></svg>
                            </div>
                            <div>
                                <p class="value">R$ <?php echo htmlspecialchars($referral_stats['total_withdrawn']); ?></p>
                                <p class="label">Total retirado</p>
                            </div>
                        </div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-box-content">
                            <div class="icon">
                                <svg fill="currentColor" viewBox="0 0 256 256" xmlns="http://www.w3.org/2000/svg"><path d="M16 152h32v56H16a8 8 0 0 1-8-8v-40a8 8 0 0 1 8-8Zm188-96a28 28 0 0 0-12 2.71h0a28 28 0 1 0-16 26.58h0A28 28 0 1 0 204 56Z" opacity="0.2"></path><path d="M230.33 141.06a24.43 24.43 0 0 0-21.24-4.23l-41.84 9.62A28 28 0 0 0 140 112H89.94a31.82 31.82 0 0 0-22.63 9.37L44.69 144H16a16 16 0 0 0-16 16v40a16 16 0 0 0 16 16h104a8 8 0 0 0 1.94-.24l64-16a7 7 0 0 0 1.19-.4L226 182.82l.44-.2a24.6 24.6 0 0 0 3.93-41.56ZM16 160h24v40H16Zm203.43 8.21-38 16.18L119 200H56v-44.69l22.63-22.62A15.86 15.86 0 0 1 89.94 128H140a12 12 0 0 1 0 24h-28a8 8 0 0 0 0 16h32a8.3 8.3 0 0 0 1.79-.2l67-15.41.31-.08a8.6 8.6 0 0 1 6.3 15.9ZM164 96a36 36 0 0 0 5.9-.48 36 36 0 1 0 28.22-47A36 36 0 1 0 164 96Zm60-12a20 20 0 1 1-20-20 20 20 0 0 1 20 20Zm-60-44a20 20 0 0 1 19.25 14.61 36 36 0 0 0-15 24.93A20.4 20.4 0 0 1 164 80a20 20 0 0 1 0-40Z"></path></svg>
                            </div>
                            <div>
                                <p class="value">R$ <?php echo htmlspecialchars($referral_stats['total_commission']); ?></p>
                                <p class="label">Total Ganho em Comissões</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-card link-card">
                <h3>Link de referência</h3>
                <div class="link-group">
                    <p class="label">Seu Código</p>
                    <div class="link-display"><span><?php echo htmlspecialchars($user_referral_code); ?></span></div>
                </div>
                <div class="link-group">
                    <p class="label">Seu Link</p>
                    <div class="link-display">
                        <span id="referral-link-text"><?php echo htmlspecialchars($base_url . $user_referral_code); ?></span>
                        <button class="copy-btn" title="Copiar" onclick="copyLink()"><svg fill="currentColor" width="18" height="18" viewBox="0 0 24 24"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"></path></svg></button>
                    </div>
                </div>
            </div>

            <hr> <div class="content-card affiliates-list-card">
                <h3>Meus Afiliados e Depósitos</h3>
                <?php if (!empty($affiliated_deposits_display_data)): ?>
                    <div class="affiliates-grid">
                        <?php foreach ($affiliated_deposits_display_data as $affiliate_id => $affiliate): ?>
                            <div class="affiliate-item">
                                <div class="affiliate-header">
                                    <span class="affiliate-name"><?php echo $affiliate['name']; ?></span>
                                    <span class="affiliate-email"><?php echo $affiliate['email']; ?></span>
                                </div>
                                <div class="affiliate-summary">
                                    <p>Total Depositado: <strong>R$ <?php echo number_format($affiliate['total_deposited'], 2, ',', '.'); ?></strong></p>
                                    <p>Comissão deste Afiliado: <strong class="commission-value">R$ <?php echo number_format($affiliate['total_commission_from_this_affiliate'], 2, ',', '.'); ?></strong></p>
                                </div>
                                <div class="affiliate-deposits-details">
                                    <h4>Depósitos Recentes:</h4>
                                    <?php if (!empty($affiliate['deposits'])): ?>
                                        <?php foreach ($affiliate['deposits'] as $deposit): ?>
                                            <div class="deposit-item">
                                                <div>
                                                    <span class="deposit-amount">R$ <?php echo $deposit['amount']; ?></span>
                                                    <span class="deposit-commission">(+R$ <?php echo $deposit['commission_earned']; ?> de comissão)</span>
                                                </div>
                                                <span class="deposit-date"><?php echo $deposit['date']; ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="no-deposits">Nenhum depósito aprovado ainda para este afiliado.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="no-affiliates-msg">Você ainda não tem afiliados que fizeram depósitos aprovados. Compartilhe seu link de referência!</p>
                <?php endif; ?>
            </div>

        <?php else: ?>

            <div class="content-card link-card">
                <h3>Link de referência</h3>
                <div class="create-code-container">
                    <div class="link-group">
                        <p class="label">Seu Código</p>
                        <div class="link-display"><span>/r/</span></div>
                    </div>
                    <button type="button" class="btn-primary" id="create-code-btn" style="width: auto; padding: 0.7rem 1.5rem;">
                        <span class="button-text">Criar Código</span>
                        <span class="button-spinner"></span>
                    </button>
                </div>
                <div class="link-group" style="margin-top: 1rem;">
                    <div class="link-display"><span><?php echo htmlspecialchars($base_url); ?></span></div>
                </div>
            </div>

        <?php endif; ?>

    </section>
</main>

<div class="levels-modal-overlay" id="levels-modal-overlay">
    <div class="levels-modal" id="levels-modal">
        <button type="button" class="levels-modal-close" id="levels-modal-close" aria-label="Fechar">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
        </button>
        <h2>Níveis de Indicação</h2>
        <div id="levels-modal-content">
            </div>
    </div>
</div>

<?php include 'withdraw-modal.php'; ?>

<script>
// Passa os dados do PHP para o JavaScript
const PHP_USER_INFO = <?php echo json_encode($user_info); ?>;
const ALL_REFERRAL_LEVELS = <?php echo json_encode($all_referral_levels_data); ?>;
const LEVEL_IMAGES_MAP = <?php echo json_encode($level_images); ?>;

document.addEventListener('DOMContentLoaded', () => {
    // --- LÓGICA EXISTENTE DO CREATE CODE ---
    const createBtn = document.getElementById('create-code-btn');
    if (createBtn) {
        createBtn.addEventListener('click', async () => {
            createBtn.classList.add('loading');
            createBtn.disabled = true;
            try {
                const response = await fetch('/api/create-referral-code.php', { method: 'POST' });
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.error || 'Ocorreu um erro desconhecido ao criar o código.');
                }
                alert('Código de referência criado com sucesso!');
                window.location.reload();
            } catch (err) {
                alert('Erro: ' + err.message);
                createBtn.classList.remove('loading');
                createBtn.disabled = false;
            }
        });
    }

    // --- NOVA LÓGICA DO SAQUE DE COMISSÃO ---
    const withdrawCommissionBtn = document.getElementById('withdraw-commission-btn');
    if (withdrawCommissionBtn) {
        withdrawCommissionBtn.addEventListener('click', e => {
            e.preventDefault();
            if (typeof openWithdrawModal === 'function') {
                openWithdrawModal();
            } else {
                console.error("A função openWithdrawModal() não está disponível.");
            }
        });
    }

    // ==========================================================
    // LÓGICA DO MODAL "VER NÍVEIS"
    // ==========================================================
    const viewLevelsBtn = document.getElementById('view-levels-btn');
    const levelsModalOverlay = document.getElementById('levels-modal-overlay');
    const levelsModal = document.getElementById('levels-modal');
    const levelsModalCloseBtn = document.getElementById('levels-modal-close');
    const levelsModalContent = document.getElementById('levels-modal-content');

    const openLevelsModal = () => {
        levelsModalContent.innerHTML = '';
        ALL_REFERRAL_LEVELS.forEach(level => {
            const isCurrentLevel = PHP_USER_INFO.level_id === level.id;
            const currentXp = PHP_USER_INFO.xp_current;
            let requirementsText = '';
            if (level.min_xp === 0) {
                requirementsText = 'Nível inicial.';
            } else {
                requirementsText = `Requisitos: ${level.min_xp} XP`;
                if (isCurrentLevel) {
                    requirementsText += ` (Você tem ${currentXp} XP)`;
                } else if (currentXp < level.min_xp) {
                    requirementsText += ` (${level.min_xp - currentXp} XP restantes)`;
                }
            }
            const levelImageUrl = LEVEL_IMAGES_MAP[level.id] || 'https://raspagreen.com/assets/default_level.png';
            levelsModalContent.innerHTML += `
                <div class="level-card-modal ${isCurrentLevel ? 'current-level' : ''}">
                    <img src="${levelImageUrl}" alt="Ícone Nível ${level.name}" class="level-icon">
                    <div class="level-info">
                        <h3>${level.name}</h3>
                        <p class="commission-rate">Comissão: ${Number(level.commission_rate * 100).toFixed(0)}%</p>
                        <p class="requirements">${requirementsText}</p>
                        ${isCurrentLevel ? '<p style="color: var(--primary-green); font-weight: 600;">Seu nível atual!</p>' : ''}
                    </div>
                </div>
            `;
        });
        levelsModalOverlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    };

    const closeLevelsModal = () => {
        levelsModalOverlay.classList.remove('show');
        document.body.style.overflow = '';
    };

    if (viewLevelsBtn) {
        viewLevelsBtn.addEventListener('click', openLevelsModal);
    }
    levelsModalCloseBtn.addEventListener('click', closeLevelsModal);
    levelsModalOverlay.addEventListener('click', closeLevelsModal);
});

// Função copyLink existente
function copyLink() {
    const linkText = document.getElementById('referral-link-text').innerText;
    navigator.clipboard.writeText(linkText).then(() => {
        alert('Link copiado para a área de transferência!');
    }, () => {
        alert('Erro ao copiar o link.');
    });
}
</script>