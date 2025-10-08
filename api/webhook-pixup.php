<?php
// public/api/webhook-pixup.php

declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método não permitido.']);
    exit;
}

require_once __DIR__ . '/../db.php';

function log_webhook_error(string $message): void {
    error_log("webhook-pixup.php: ERRO - " . $message);
}

function log_webhook_info(string $message): void {
    error_log("webhook-pixup.php: INFO - " . $message);
}

try {
    $input = file_get_contents('php://input');
    $payload = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE || !isset($payload['requestBody']['status'], $payload['requestBody']['external_id'])) {
        http_response_code(400);
        log_webhook_error("Payload inválido ou incompleto. Input: " . $input);
        echo json_encode(['ok' => false, 'error' => 'Payload inválido.']);
        exit;
    }

    $requestBody = $payload['requestBody'];
    $status = $requestBody['status'];
    $externalId = $requestBody['external_id'];
    $amount = (float)$requestBody['amount'];

    if ($requestBody['transactionType'] !== 'RECEIVEPIX') {
        http_response_code(200);
        echo json_encode(['ok' => true, 'message' => 'Tipo de transação não é "RECEIVEPIX", ignorado.']);
        exit;
    }

    if ($status === 'PAID') {
        log_webhook_info("Pagamento confirmado para a transação local ID: {$externalId}. Valor: R$ {$amount}");

        $pdo = db();

        try {
            $pdo->beginTransaction();

            $stmt_check_local = $pdo->prepare("SELECT id, user_id, amount, status FROM transactions WHERE id = ? AND provider = 'PixUp' AND status = 'PENDING' FOR UPDATE");
            $stmt_check_local->execute([$externalId]);
            $localTransaction = $stmt_check_local->fetch(PDO::FETCH_ASSOC);

            if ($localTransaction) {
                $userId = (int)$localTransaction['user_id'];
                $depositAmount = (float)$localTransaction['amount'];
                $valor_a_creditar = $depositAmount * 3;

                $updateUserStmt = $pdo->prepare("UPDATE users SET saldo = saldo + ?, total_deposited = total_deposited + ?, rollover_amount = rollover_amount + ? WHERE id = ?");
                $updateUserStmt->execute([$valor_a_creditar, $depositAmount, $valor_a_creditar, $userId]);

                $updateTxStmt = $pdo->prepare("UPDATE transactions SET status = 'APPROVED', updated_at = NOW() WHERE id = ?");
                $updateTxStmt->execute([$externalId]);

                $stmt_depositant_referrer = $pdo->prepare("SELECT referrer_id, name FROM users WHERE id = ?");
                $stmt_depositant_referrer->execute([$userId]);
                $depositantData = $stmt_depositant_referrer->fetch(PDO::FETCH_ASSOC);

                if ($depositantData && !empty($depositantData['referrer_id'])) {
                    $referrerId = (int)$depositantData['referrer_id'];
                    $depositantName = $depositantData['name'] ?? "Usuário #{$userId}";

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
                            $insertCommissionTxStmt->execute([$referrerId, $userId, 'deposit_commission', $commissionAmount, "Comissão de depósito de R$ " . number_format($depositAmount, 2, ',', '.') . " do indicado " . $depositantName]);
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
                log_webhook_info("Transação ID {$externalId} processada com sucesso. Saldo do usuário {$userId} atualizado.");
            } else {
                log_webhook_info("Transação ID {$externalId} não encontrada, já processada ou não é do provider 'pixup'.");
            }

        } catch (PDOException $e) {
            $pdo->rollBack();
            log_webhook_error("Erro de PDO ao processar o evento. Mensagem: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Erro interno do servidor.']);
            exit;
        }

    } else {
        log_webhook_info("Webhook recebido para a transação local ID {$externalId}, mas o status não é PAID. Status: {$status}.");
    }

    http_response_code(200);
    echo json_encode(['ok' => true, 'message' => 'Evento recebido e processado.']);

} catch (Exception $e) {
    log_webhook_error("Erro geral ao processar o webhook: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Erro interno do servidor.']);
}