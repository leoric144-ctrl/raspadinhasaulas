<?php
// api/financeconsult.php
// Varre todos os depósitos PENDING (type='deposit') do usuário logado.

declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'UNAUTHORIZED', 'error' => 'Usuário não autenticado.']);
    exit;
}

require_once __DIR__ . '/../db.php';

// === Constantes das APIs (use variáveis de ambiente em produção) ==
$IRONPAY_API_TOKEN = '#';
$IRONPAY_ENDPOINT = 'https://api.ironpayapp.com.br/api';
$ZEROONEPAY_API_TOKEN = '#';
$ZEROONEPAY_ENDPOINT = 'https://api.zeroonepay.com.br/api'; // <--- AJUSTADO para o endpoint de transações unificado

// === Helpers de mapeamento ===
function mapApiStatus(?string $s): string // Função unificada para IronPay e ZeroOnePay
{
    $s = strtolower($s ?? 'unknown');
    if ($s === 'paid') return 'APPROVED';
    if ($s === 'approved') return 'APPROVED';
    if ($s === 'completed') return 'APPROVED';
    return strtoupper($s);
}

// === Comissão/XP (Mantida inalterada) ===
function aplicarComissaoEExp(PDO $pdo, int $depositingUserId, float $depositAmount): void
{
    $stmt = $pdo->prepare("SELECT referrer_id, name FROM users WHERE id = ?");
    $stmt->execute([$depositingUserId]);
    $dep = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$dep || empty($dep['referrer_id'])) return;

    $referrerId     = (int)$dep['referrer_id'];
    $depositantName = $dep['name'] ?? ("Usuário #{$depositingUserId}");

    $sr = $pdo->prepare("SELECT commission_rate, xp, level_id, is_level_manual_override FROM users WHERE id = ?");
    $sr->execute([$referrerId]);
    $ref = $sr->fetch(PDO::FETCH_ASSOC);
    if (!$ref) return;

    $rate     = (float)$ref['commission_rate'];
    $bonus    = $depositAmount * $rate;
    $oldXp    = (int)$ref['xp'];
    $oldLevel = (int)$ref['level_id'];
    $isManuallySet = (bool)$ref['is_level_manual_override'];
    $gainXp   = 100;
    $newXp    = $oldXp + $gainXp;

    if ($bonus > 0) {
        $up = $pdo->prepare("
            UPDATE users
                SET commission_balance = commission_balance + ?,
                    total_commission_earned = total_commission_earned + ?,
                    xp = ?
              WHERE id = ?");
        $up->execute([$bonus, $bonus, $newXp, $referrerId]);

        $ins = $pdo->prepare("
            INSERT INTO commission_transactions
                (user_id, referred_user_id, type, amount, description, status)
            VALUES (?, ?, 'deposit_commission', ?, ?, 'completed')");
        $desc = "Comissão de depósito de R$ " . number_format($depositAmount, 2, ',', '.') .
            " do indicado " . $depositantName;
        $ins->execute([$referrerId, $depositingUserId, $bonus, $desc]);
    } else {
        $up = $pdo->prepare("UPDATE users SET xp = ? WHERE id = ?");
        $up->execute([$newXp, $referrerId]);
    }

    if (!$isManuallySet) {
        try {
            // Tentativa de usar a tabela 'deposits' (se existir)
            $q = $pdo->prepare("SELECT COUNT(DISTINCT d.user_id) FROM users u JOIN deposits d ON u.id = d.user_id WHERE u.referrer_id = ? AND d.status = 'APPROVED'");
            $q->execute([$referrerId]);
            $active = (int)$q->fetchColumn();
        } catch (PDOException $e) {
            // Fallback para usar a tabela 'transactions'
            $q = $pdo->prepare("SELECT COUNT(DISTINCT t.user_id) FROM users u JOIN transactions t ON u.id = t.user_id WHERE u.referrer_id = ? AND UPPER(COALESCE(t.type,'')) = 'DEPOSIT' AND t.status = 'APPROVED'");
            $q->execute([$referrerId]);
            $active = (int)$q->fetchColumn();
        }

        $levels = $pdo->query("SELECT id, min_xp, min_active_indications, commission_rate FROM referral_levels ORDER BY min_xp ASC, min_active_indications ASC")
            ->fetchAll(PDO::FETCH_ASSOC);

        $newLevel = $oldLevel;
        $newRate  = $rate;
        foreach ($levels as $lv) {
            if ($newXp >= (int)$lv['min_xp'] && $active >= (int)$lv['min_active_indications']) {
                $newLevel = (int)$lv['id'];
                $newRate  = (float)$lv['commission_rate'];
            } else {
                break;
            }
        }
        if ($newLevel > $oldLevel) {
            $u = $pdo->prepare("UPDATE users SET level_id = ?, commission_rate = ? WHERE id = ?");
            $u->execute([$newLevel, $newRate, $referrerId]);
        }
    }
}

$userId = (int)$_SESSION['user_id'];

$result = [
    'status'              => 'OK',
    'checked'             => 0,
    'approved'            => 0,
    'still_pending'       => 0,
    'errors'              => [],
    'approved_ids'        => [],
    'pending_ids'         => [],
    'approved_detail'     => [],
    'pending_detail'      => [],
];

try {
    $pdo = db();

    $stmt = $pdo->prepare("
        SELECT id
          FROM transactions
         WHERE user_id = ?
           AND status  = 'PENDING'
           AND type    = 'deposit'
           AND provider IN ('IronPay', 'ZeroOnePay')
      ORDER BY id ASC
    ");
    $stmt->execute([$userId]);
    $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!$ids) {
        echo json_encode($result);
        exit;
    }

    foreach ($ids as $id) {
        $result['checked']++;

        $pdo->beginTransaction();
        $lock = $pdo->prepare("
            SELECT id, user_id, amount, status, provider, provider_hash, provider_transaction_id
              FROM transactions
             WHERE id = ?
               FOR UPDATE SKIP LOCKED
        ");
        $lock->execute([$id]);
        $tx = $lock->fetch(PDO::FETCH_ASSOC);

        if (!$tx) {
            $pdo->rollBack();
            continue;
        }
        if ($tx['status'] !== 'PENDING') {
            $pdo->commit();
            continue;
        }

        $provider = $tx['provider'];
        $api_status = 'UNKNOWN';
        $prov_raw = null;

        try {
            // === LÓGICA UNIFICADA DE CONSULTA ===
            if ($provider === 'IronPay' || $provider === 'ZeroOnePay') {

                // 1. Configuração de Endpoints e Tokens
                if ($provider === 'IronPay') {
                    $endpoint = $IRONPAY_ENDPOINT;
                    $token = $IRONPAY_API_TOKEN;
                } else { // ZeroOnePay
                    $endpoint = $ZEROONEPAY_ENDPOINT;
                    $token = $ZEROONEPAY_API_TOKEN;
                }

                // O ID usado na consulta deve ser o HASH do provedor para seguir a lógica IronPay
                $hash = $tx['provider_hash'] ?? '';
                if ($hash === '') {
                    // Fallback para o ID da transação (pode ser necessário dependendo da API)
                    $hash = $tx['provider_transaction_id'] ?? '';
                    if ($hash === '') {
                         throw new Exception("Hash/ID do {$provider} não encontrado para tx {$tx['id']}");
                    }
                }

                // 2. Montagem da URL (padrão IronPay/ZeroOnePay unificado)
                $api_url = "{$endpoint}/public/v1/transactions/{$hash}?api_token={$token}";

                // 3. Execução do cURL
                $ch = curl_init($api_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($ch, CURLOPT_TIMEOUT, 20);

                $resp = curl_exec($ch);
                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($code !== 200) {
                    throw new Exception("Falha ao consultar {$provider}. HTTP {$code} | {$resp}");
                }

                $data = json_decode($resp, true);

                // 4. Mapeamento de Status
                // Ambas as APIs, ao usarem a mesma rota, devem retornar 'payment_status'
                $prov_raw = $data['payment_status'] ?? 'UNKNOWN';
                $api_status = mapApiStatus($prov_raw);

            }
            // === FIM DA LÓGICA UNIFICADA ===

            if ($api_status === 'APPROVED') {
                $localId   = (int)$tx['id'];
                $userIdTx  = (int)$tx['user_id'];
                $depAmount = (float)$tx['amount'];
                $valor_a_creditar = $depAmount * 3;

                $upUser = $pdo->prepare("UPDATE users SET saldo = saldo + ?, total_deposited = total_deposited + ?, rollover_amount = rollover_amount + ? WHERE id = ?");
                $upUser->execute([$valor_a_creditar, $depAmount, $valor_a_creditar, $userIdTx]);

                $upTx = $pdo->prepare("UPDATE transactions SET status = 'APPROVED', updated_at = NOW() WHERE id = ?");
                $upTx->execute([$localId]);

                aplicarComissaoEExp($pdo, $userIdTx, $depAmount);
                $pdo->commit();

                $result['approved']++;
                $result['approved_ids'][] = $localId;
                $result['approved_detail'][] = [
                    'id'                        => $localId,
                    'amount'                    => $depAmount,
                    'provider'                  => $provider,
                    'provider_hash'             => $tx['provider_hash'],
                    'provider_transaction_id'   => $tx['provider_transaction_id'],
                    'status_from_provider'      => $prov_raw,
                ];
            } else {
                $pdo->commit();
                $result['still_pending']++;
                $result['pending_ids'][] = (int)$tx['id'];
                $result['pending_detail'][] = [
                    'id'                        => (int)$tx['id'],
                    'amount'                    => (float)$tx['amount'],
                    'provider'                  => $provider,
                    'provider_hash'             => $tx['provider_hash'],
                    'provider_transaction_id'   => $tx['provider_transaction_id'],
                    'status_from_provider'      => $prov_raw,
                ];
            }
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $result['errors'][] = [
                'transaction_id' => (int)$tx['id'],
                'message'        => $ex->getMessage()
            ];
        }
    }
    echo json_encode($result);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'ERROR', 'error' => 'Erro interno do servidor.', 'detail' => $e->getMessage()]);
}
