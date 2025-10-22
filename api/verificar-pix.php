<?php
// api/verificar-pix.php - Versão universal e 100% completa

declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'UNAUTHORIZED', 'error' => 'Usuário não autenticado.']);
    exit;
}

require_once __DIR__ . '/../db.php';

$transaction_id_ou_hash = $_GET['id'] ?? null;
if (empty($transaction_id_ou_hash)) {
    http_response_code(400);
    echo json_encode(['status' => 'ERROR', 'error' => 'ID ou Hash da transação não fornecido.']);
    exit;
}

// Constantes das APIs (Mantenha seus tokens em variáveis de ambiente em produção)
$ZEROONEPAY_API_TOKEN = '#';
$IRONPAY_API_TOKEN = '#';

// Endpoints base
$IRONPAY_ENDPOINT = 'https://api.ironpayapp.com.br/api';
$ZEROONEPAY_ENDPOINT = 'https://api.zeroonepay.com.br/api'; // Ajustado para seguir o padrão de endpoint da IronPay

try {
    $pdo = db();
    $pdo->beginTransaction();

    $stmt_check_local = $pdo->prepare(
        "SELECT id, user_id, amount, status, provider, provider_hash, provider_transaction_id
         FROM transactions
         WHERE (provider_transaction_id = ? OR provider_hash = ?) AND type = 'deposit' FOR UPDATE"
    );
    $stmt_check_local->execute([$transaction_id_ou_hash, $transaction_id_ou_hash]);
    $localTransaction = $stmt_check_local->fetch(PDO::FETCH_ASSOC);

    if (!$localTransaction) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(['status' => 'NOT_FOUND', 'error' => 'Transação não encontrada localmente.']);
        exit;
    }

    if ($localTransaction['status'] === 'APPROVED') {
        $pdo->commit();
        echo json_encode(['status' => 'PAID']);
        exit();
    }

    $providerName = $localTransaction['provider'];
    $api_status = 'UNKNOWN';
    $response = null;
    $http_status = null;
    $responseData = [];

    // ================== LÓGICA UNIFICADA: IRONPAY & ZEROONEPAY ==================
    if ($providerName === 'IronPay' || $providerName === 'ZeroOnePay') {

        // 1. Configuração Dinâmica de Tokens e Endpoints
        if ($providerName === 'IronPay') {
            $api_endpoint = $IRONPAY_ENDPOINT;
            $api_token = $IRONPAY_API_TOKEN;
            // A IronPay usa o provider_hash para consulta de status
            $id_to_use = $localTransaction['provider_hash'];
            if (empty($id_to_use)) throw new Exception("Hash da IronPay não encontrado para a transação local ID: {$localTransaction['id']}");

        } elseif ($providerName === 'ZeroOnePay') {
            $api_endpoint = $ZEROONEPAY_ENDPOINT;
            $api_token = $ZEROONEPAY_API_TOKEN;
            // Assumimos que a ZeroOnePay AGORA usa o provider_hash para seguir o padrão IronPay
            // Se necessário usar o provider_transaction_id, mude a linha abaixo para: $localTransaction['provider_transaction_id'];
            $id_to_use = $localTransaction['provider_hash'];
            if (empty($id_to_use)) throw new Exception("Hash/ID da ZeroOnePay não encontrado para a transação local ID: {$localTransaction['id']}");
        }

        // 2. Montagem da URL (Padrão IronPay: /public/v1/transactions/{id}?api_token=...)
        $api_url = "{$api_endpoint}/public/v1/transactions/{$id_to_use}?api_token={$api_token}";

        // 3. Execução do cURL (GET - Unificado)
        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 25);

        $response = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_status !== 200) {
            error_log("Falha ao consultar {$providerName}. HTTP: {$http_status} | Response: {$response}");
            throw new Exception("Falha ao consultar {$providerName}. HTTP: {$http_status} | " . ($response ?: 'Sem resposta da API'));
        }

        $responseData = json_decode($response, true);

        // 4. Mapeamento de Status
        $api_status_from_provider = $responseData['payment_status'] ?? 'UNKNOWN';
        $api_status = (strtolower($api_status_from_provider) === 'paid') ? 'APPROVED' : strtoupper($api_status_from_provider);

    // ================== LÓGICA PIXUP (MANTIDA) ==================
    } elseif ($providerName === 'PixUp') {
        // Para PixUp, não há um endpoint de consulta. O status é atualizado
        // por um Webhook assíncrono. O script de verificação não fará nada.
        $api_status = 'PENDING';
    } else {
        throw new Exception("Provedor '{$providerName}' desconhecido ou não implementado.");
    }

    // ================== PROCESSAMENTO APÓS CONSULTA ==================
    // (O restante da lógica de aprovação e comissão é mantido inalterado)
    if ($api_status === 'APPROVED') {
        $localTransactionId = $localTransaction['id'];
        $depositAmount = (float)$localTransaction['amount'];
        $depositingUserId = (int)$localTransaction['user_id'];
        $valor_a_creditar = $depositAmount * 3;

        $updateUserStmt = $pdo->prepare("UPDATE users SET saldo = saldo + ?, total_deposited = total_deposited + ?, rollover_amount = rollover_amount + ? WHERE id = ?");
        $updateUserStmt->execute([$valor_a_creditar, $depositAmount, $valor_a_creditar, $depositingUserId]);

        $updateTxStmt = $pdo->prepare("UPDATE transactions SET status = 'APPROVED', updated_at = NOW() WHERE id = ?");
        $updateTxStmt->execute([$localTransactionId]);

        $stmt_depositant_referrer = $pdo->prepare("SELECT referrer_id, name FROM users WHERE id = ?");
        $stmt_depositant_referrer->execute([$depositingUserId]);
        $depositantData = $stmt_depositant_referrer->fetch(PDO::FETCH_ASSOC);

        if ($depositantData && !empty($depositantData['referrer_id'])) {
            $referrerId = (int)$depositantData['referrer_id'];
            $depositantName = $depositantData['name'] ?? "Usuário #{$depositingUserId}";

            $stmt_referrer_data = $pdo->prepare("SELECT commission_rate, xp, level_id, is_level_manual_override FROM users WHERE id = ?");
            $stmt_referrer_data->execute([$referrerId]);
            $referrerData = $stmt_referrer_data->fetch(PDO::FETCH_ASSOC);

            if ($referrerData) {
                $commissionRate = (float)$referrerData['commission_rate'];
                $commissionAmount = $depositAmount * $commissionRate;
                $currentReferrerXp = (int)$referrerData['xp'];
                $currentReferrerLevelId = (int)$referrerData['level_id'];
                $isManuallySet = (bool)$referrerData['is_level_manual_override'];
                $xp_gained_from_deposit = 100;
                $newReferrerXp = $currentReferrerXp + $xp_gained_from_deposit;

                if ($commissionAmount > 0) {
                    $updateReferrerStmt = $pdo->prepare("UPDATE users SET commission_balance = commission_balance + ?, total_commission_earned = total_commission_earned + ?, xp = ? WHERE id = ?");
                    $updateReferrerStmt->execute([$commissionAmount, $commissionAmount, $newReferrerXp, $referrerId]);

                    $insertCommissionTxStmt = $pdo->prepare("INSERT INTO commission_transactions (user_id, referred_user_id, type, amount, description, status) VALUES (?, ?, ?, ?, ?, 'completed')");
                    $insertCommissionTxStmt->execute([$referrerId, $depositingUserId, 'deposit_commission', $commissionAmount, "Comissão de depósito de R$ " . number_format($depositAmount, 2, ',', '.') . " do indicado " . $depositantName]);
                } else {
                    $updateReferrerXpOnlyStmt = $pdo->prepare("UPDATE users SET xp = ? WHERE id = ?");
                    $updateReferrerXpOnlyStmt->execute([$newReferrerXp, $referrerId]);
                }

                if (!$isManuallySet) {
                    $stmt_active_referrals = $pdo->prepare("SELECT COUNT(DISTINCT d.user_id) FROM users u JOIN transactions d ON u.id = d.user_id WHERE u.referrer_id = ? AND d.status = 'APPROVED'");
                    $stmt_active_referrals->execute([$referrerId]);
                    $activeReferralsCount = (int)$stmt_active_referrals->fetchColumn();

                    $stmt_all_levels = $pdo->prepare("SELECT id, min_xp, min_active_indications, commission_rate FROM referral_levels ORDER BY min_xp ASC, min_active_indications ASC");
                    $stmt_all_levels->execute();
                    $all_levels = $stmt_all_levels->fetchAll(PDO::FETCH_ASSOC);

                    $new_level_id = $currentReferrerLevelId;
                    $new_commission_rate = $commissionRate;

                    foreach ($all_levels as $level) {
                        if ($newReferrerXp >= (int)$level['min_xp'] && $activeReferralsCount >= (int)$level['min_active_indications']) {
                            $new_level_id = (int)$level['id'];
                            $new_commission_rate = (float)$level['commission_rate'];
                        } else {
                            break;
                        }
                    }

                    if ($new_level_id > $currentReferrerLevelId) {
                        $updateLevelStmt = $pdo->prepare("UPDATE users SET level_id = ?, commission_rate = ?, is_level_manual_override = FALSE WHERE id = ?");
                        $updateLevelStmt->execute([$new_level_id, $new_commission_rate, $referrerId]);
                    }
                }
            }
        }

        $pdo->commit();
        echo json_encode(['status' => 'PAID']);

    } else {
        $pdo->rollBack();
        echo json_encode(['status' => $api_status]);
    }

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("verificar-pix.php: ERRO CRÍTICO. Mensagem: " . $e->getMessage());
    http_response_code(500);
    $errorDetail = getenv('APP_ENV') === 'development' ? $e->getMessage() : 'Consulte os logs do servidor.';
    echo json_encode(['status' => 'ERROR', 'error' => 'Erro interno do servidor.', 'detail' => $errorDetail]);
}
