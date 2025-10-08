<?php
// /views/main-content-historico-de-jogos.php

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
$game_history = [];
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) { $page = 1; }
$offset = ($page - 1) * $limit;

try {
    $pdo = db();

    // Count total records in the historicplay table for the current user
    $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM historicplay WHERE user_id = ?");
    $stmt_count->execute([$user_id]);
    $total_records = $stmt_count->fetchColumn();
    $total_pages = $total_records > 0 ? ceil($total_records / $limit) : 1;

    // Fetch the paginated game history for the current user
    $stmt_history = $pdo->prepare(
        "SELECT bet_amount, action, game_name, played_at, round_id, prize_amount
         FROM historicplay WHERE user_id = ?
         ORDER BY played_at DESC LIMIT ? OFFSET ?"
    );
    $stmt_history->bindValue(1, $user_id, PDO::PARAM_INT);
    $stmt_history->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt_history->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt_history->execute();
    $game_history = $stmt_history->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    // For debugging, you can uncomment the line below
    // echo "Erro: " . $e->getMessage();
    echo "Erro ao carregar o histórico de jogos.";
}
?>

<style>
/* Styles for the Game History Page */
.history-content-wrapper{padding:1.5rem; color: #f0f0f0;}
.history-header h1{font-size:1.75rem;margin:0 0 1.5rem 0;}
.table-wrapper{overflow-x:auto}
.history-table{width:100%;border-collapse:collapse;color:#f0f0f0}
.history-table th,.history-table td{padding:1rem;text-align:left;border-bottom:1px solid #27272a;white-space:nowrap;font-size:.9rem}
.history-table th{font-size:.75rem;color:#a0a0a0;text-transform:uppercase;font-weight:600}
.history-table td{color:#a0a0a0; font-weight: 500;}
.history-table td:first-child { font-weight: 600; color: #f0f0f0; }

.status-pill{display:inline-block;padding:.25rem .75rem;border-radius:99px;font-size:.8rem;font-weight:700}
.status-pill.ganhou{background-color:rgba(16,185,129,.1);color:#10b981}
.status-pill.perdeu{background-color:rgba(239,68,68,.1);color:#ef4444}

.pagination-controls{display:flex;justify-content:space-between;margin-top:10.5rem;color:#a0a0a0;font-size:12px;}
.pagination-buttons{display:flex; align-items:center; gap:.5rem; flex-wrap: wrap; justify-content:center; flex-direction:row; padding: 0 4px;}
.pagination-buttons a,.pagination-buttons span{padding:.5rem 1rem;border:1px solid #27272a;border-radius:8px;font-weight:500;text-decoration:none;color:#a0a0a0;background-color:#1F1F1F}
.pagination-buttons a.current-page{background:var(--primary-color, #28e504);color:#000;border-color:var(--primary-color, #28e504)}
.pagination-buttons a.disabled{pointer-events:none;opacity:.5}
.actions-cell button { background: none; border: none; color: #a0a0a0; cursor: pointer; padding: 0.5rem; }

@media (max-width:768px){
    .history-content-wrapper{padding:1rem}
    .history-header h1{font-size:1.5rem}
    .pagination-controls{flex-direction:column;gap:1rem}
    .history-table th,.history-table td{padding:.75rem .5rem; font-size: 0.85rem;}
}
</style>

<div class="history-content-wrapper">
    <div class="history-header">
        <h1>Histórico de Jogos</h1>
    </div>
    <div class="table-wrapper">
        <table class="history-table">
            <thead>
                <tr>
                    <th>Valor</th>
                    <th>Ação</th>
                    <th>Jogo</th>
                    <th>Data/Hora</th>
                    <th>ID Rodada</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($game_history)): ?>
                    <tr><td colspan="6" style="text-align:center; padding: 2rem;">Nenhum jogo encontrado no histórico.</td></tr>
                <?php else: ?>
                    <?php foreach ($game_history as $play): ?>
                        <tr>
                            <td>R$ <?= htmlspecialchars(number_format((float)$play['bet_amount'], 2, ',', '.')) ?></td>
                            <td>
                                <?php
                                $action_class = strtolower($play['action']) === 'ganhou' ? 'ganhou' : 'perdeu';
                                $action_text = $play['action'];
                                if($action_class === 'ganhou' && $play['prize_amount'] > 0){
                                    $action_text .= ' (+ R$ ' . number_format((float)$play['prize_amount'], 2, ',', '.') . ')';
                                }
                                ?>
                                <span class="status-pill <?= $action_class ?>"><?= htmlspecialchars($action_text) ?></span>
                            </td>
                            <td>Raspadinha</td>
                            <td>
                                <?php
                                    // 1. Cria o objeto DateTime informando que a string do banco está em UTC.
                                    $date = new DateTime($play['played_at'], new DateTimeZone('UTC'));

                                    // 2. Converte o objeto para o fuso horário de São Paulo.
                                    $date->setTimezone(new DateTimeZone('America/Sao_Paulo'));

                                    // 3. Exibe a data/hora corretamente formatada.
                                    echo $date->format('d/m/Y, H:i:s');
                                ?>
                            </td>
                            <td><?= htmlspecialchars($play['round_id']) ?></td>
                            <td class="actions-cell">
                                <button aria-label="Abrir ações" style="opacity: 0.5; cursor: default;">
                                    <svg fill="currentColor" width="20" height="20" viewBox="0 0 24 24"><path d="M12 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0-6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 12c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"></path></svg>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination-controls">
        <div class="records-info">
            <span>Mostrando <br> <?= count($game_history) ?> de <?= $total_records ?> registros</span>
        </div>
        <div class="pagination-buttons">
            <a href="?view=historico&page=<?= max(1, $page - 1); ?>" class="<?= ($page <= 1) ? 'disabled' : '' ?>">Anterior</a>

            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?view=historico&page=<?= $i; ?>" class="<?= ($page == $i) ? 'current-page' : '' ?>"><?= $i; ?></a>
            <?php endfor; ?>

            <a href="?view=historico&page=<?= min($total_pages, $page + 1); ?>" class="<?= ($page >= $total_pages) ? 'disabled' : '' ?>">Próximo</a>
        </div>
    </div>
</div>