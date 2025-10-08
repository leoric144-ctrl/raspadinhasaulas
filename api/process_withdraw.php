<?php
// public/api/process_withdraw.php

// ✅ CORREÇÃO: Suprime a exibição de erros para garantir uma saída JSON limpa.
ini_set('display_errors', 0);
error_reporting(0);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ✅ BOA PRÁTICA: Definir o cabeçalho JSON no início do script.
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-control-allow-headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../db.php';
date_default_timezone_set('America/Sao_Paulo');

$response = ['success' => false, 'message' => 'Erro desconhecido.'];

// ✅ BOA PRÁTICA: Definir valores padrão em um só lugar.
define('MIN_WITHDRAW_VALUE', 30.00);
define('MAX_WITHDRAW_VALUE', 1000.00);
define('COMMISSION_TRANSFER_ROLLOVER_MULTIPLIER', 1.0);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response['message'] = 'Método não permitido.';
    echo json_encode($response);
    exit;
}

// ✅ SEGURANÇA: Valida o user_id logo no início.
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

// ✅ SEGURANÇA: Validar se o JSON é válido.
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Requisição inválida (JSON malformado).']);
    exit;
}

// ✅ CORREÇÃO: Substituindo FILTER_SANITIZE_STRING obsoleto por htmlspecialchars e validações mais estritas.
$amount = filter_var($data['amount'] ?? 0, FILTER_VALIDATE_FLOAT);
$balance_type = isset($data['balance_type']) ? htmlspecialchars($data['balance_type'], ENT_QUOTES, 'UTF-8') : '';
$transfer_to_main = filter_var($data['transfer_to_main'] ?? false, FILTER_VALIDATE_BOOLEAN);

// Validações de entrada iniciais
if (!in_array($balance_type, ['main', 'commission'])) {
    http_response_code(400);
    $response['message'] = 'Tipo de saldo inválido.';
    echo json_encode($response);
    exit;
}

if ($amount === false || $amount <= 0) {
    http_response_code(400);
    $response['message'] = 'O valor fornecido é inválido.';
    echo json_encode($response);
    exit;
}

// Lógica de saque PIX (não transferência) exige mais campos
if (!$transfer_to_main) {
    $pix_key_type = isset($data['pix_key_type']) ? htmlspecialchars($data['pix_key_type'], ENT_QUOTES, 'UTF-8') : '';
    $pix_key = isset($data['pix_key']) ? htmlspecialchars($data['pix_key'], ENT_QUOTES, 'UTF-8') : '';
    $document_provided = isset($data['document']) ? preg_replace("/[^0-9]/", "", $data['document']) : '';

    if (empty($pix_key_type) || empty($pix_key) || empty($document_provided)) {
        http_response_code(400);
        $response['message'] = 'Para saques PIX, a chave PIX, o tipo da chave e o CPF são obrigatórios.';
        echo json_encode($response);
        exit;
    }

    if ($amount < MIN_WITHDRAW_VALUE) {
        http_response_code(400);
        $response['message'] = 'Valor de saque abaixo do mínimo permitido (R$ ' . number_format(MIN_WITHDRAW_VALUE, 2, ',', '.') . ').';
        echo json_encode($response);
        exit;
    }

    if ($amount > MAX_WITHDRAW_VALUE) {
        http_response_code(400);
        $response['message'] = 'Valor de saque acima do máximo permitido (R$ ' . number_format(MAX_WITHDRAW_VALUE, 2, ',', '.') . ').';
        echo json_encode($response);
        exit;
    }
}

try {
    $pdo = db();
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT saldo, commission_balance, document, rollover_amount, is_demo FROM users WHERE id = ? FOR UPDATE");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $pdo->rollBack();
        http_response_code(404);
        $response['message'] = 'Usuário não encontrado.';
        echo json_encode($response);
        exit;
    }

    if ($user['is_demo']) {
        $pdo->rollBack();
        http_response_code(403);
        $response['message'] = 'Não é possível realizar saques em uma conta de demonstração.';
        echo json_encode($response);
        exit;
    }

    $current_main_balance = (float) $user['saldo'];
    $current_commission_balance = (float) $user['commission_balance'];
    $current_rollover_amount = (float) $user['rollover_amount'];

    // Lógica de Transferência de Comissão para Saldo Principal
    if ($balance_type === 'commission' && $transfer_to_main) {
        if ($current_commission_balance < $amount) {
            $pdo->rollBack();
            http_response_code(400);
            $response['message'] = 'Saldo de comissão insuficiente para realizar a transferência.';
            echo json_encode($response);
            exit;
        }

        $rollover_to_add = $amount * COMMISSION_TRANSFER_ROLLOVER_MULTIPLIER;

        $stmtUpdate = $pdo->prepare("UPDATE users SET commission_balance = commission_balance - ?, saldo = saldo + ?, rollover_amount = rollover_amount + ?, updated_at = NOW() WHERE id = ?");
        $stmtUpdate->execute([$amount, $amount, $rollover_to_add, $user_id]);

        $transaction_ref_id = 'TRF-COM-' . date('Ymd-His') . '-' . strtoupper(bin2hex(random_bytes(2)));
        $description = 'Transferência de saldo de comissão para saldo principal (rollover aplicado)';

        $stmtLog = $pdo->prepare("INSERT INTO transactions (user_id, amount, status, type, description, created_at, provider_transaction_id) VALUES (?, ?, 'COMPLETED', 'COMMISSION_TO_MAIN_TRANSFER', ?, NOW(), ?)");
        $stmtLog->execute([$user_id, $amount, $description, $transaction_ref_id]);

        $pdo->commit();
        $response = [
            'success' => true,
            'message' => 'Transferência para o saldo principal realizada com sucesso!',
            'amount' => $amount,
            'new_main_balance' => $current_main_balance + $amount,
            'new_commission_balance' => $current_commission_balance - $amount,
            'transaction_ref_id' => $transaction_ref_id
        ];

    } else { // Lógica de Saque para Chave PIX
        $user_db_document_clean = preg_replace("/[^0-9]/", "", $user['document'] ?? '');
        if ($document_provided !== $user_db_document_clean) {
            $pdo->rollBack();
            http_response_code(403);
            $response['message'] = 'Erro de segurança: CPF fornecido não corresponde ao seu CPF cadastrado.';
            echo json_encode($response);
            exit;
        }

        if ($balance_type === 'main' && $current_rollover_amount > 0.01) { // Usar uma pequena margem para evitar problemas com float
            $pdo->rollBack();
            http_response_code(403);
            $response['message'] = 'Você ainda possui um rollover de R$ ' . number_format($current_rollover_amount, 2, ',', '.') . ' a ser cumprido antes de poder sacar.';
            echo json_encode($response);
            exit;
        }

        $balance_to_use = ($balance_type === 'main') ? $current_main_balance : $current_commission_balance;
        if ($balance_to_use < $amount) {
            $pdo->rollBack();
            http_response_code(400);
            $response['message'] = 'Saldo insuficiente para realizar o saque.';
            echo json_encode($response);
            exit;
        }

        $withdrawal_ref_id = 'SAQ-' . date('Ymd-His') . '-' . strtoupper(bin2hex(random_bytes(2)));

        if ($balance_type === 'main') {
            $new_main_balance = $current_main_balance - $amount;
            $new_commission_balance = $current_commission_balance;
            $update_stmt = $pdo->prepare("UPDATE users SET saldo = ? WHERE id = ?");
            $update_stmt->execute([$new_main_balance, $user_id]);
            $transaction_type = 'WITHDRAWAL';
            $transaction_description = 'Saque do Saldo Principal para PIX ' . strtoupper($pix_key_type);
        } else { // commission
            $new_main_balance = $current_main_balance;
            $new_commission_balance = $current_commission_balance - $amount;
            $update_stmt = $pdo->prepare("UPDATE users SET commission_balance = ?, total_commission_withdrawn = total_commission_withdrawn + ? WHERE id = ?");
            $update_stmt->execute([$new_commission_balance, $amount, $user_id]);
            $transaction_type = 'COMMISSION_WITHDRAWAL';
            $transaction_description = 'Saque do Saldo de Comissão para PIX ' . strtoupper($pix_key_type);

            $stmtLogCommission = $pdo->prepare("INSERT INTO commission_transactions (user_id, type, amount, status, description, created_at) VALUES (?, 'WITHDRAWAL', ?, 'PENDING', ?, NOW())");
            $stmtLogCommission->execute([$user_id, -$amount, $transaction_description]);
        }

        $pix_key_cleaned = in_array($pix_key_type, ['cpf', 'phone', 'cnpj']) ? preg_replace("/[^0-9]/", "", $pix_key) : $pix_key;

        $stmtWithdrawal = $pdo->prepare("INSERT INTO withdrawals (user_id, amount, pix_key_type, pix_key, status, created_at, withdrawal_ref_id) VALUES (?, ?, ?, ?, 'PENDING', NOW(), ?)");
        $stmtWithdrawal->execute([$user_id, $amount, $pix_key_type, $pix_key_cleaned, $withdrawal_ref_id]);
        $withdrawal_id = $pdo->lastInsertId();

        $stmtLog = $pdo->prepare("INSERT INTO transactions (user_id, amount, status, type, description, created_at, withdrawal_id, provider_transaction_id) VALUES (?, ?, 'PENDING', ?, ?, NOW(), ?, ?)");
        $stmtLog->execute([$user_id, -$amount, $transaction_type, $transaction_description, $withdrawal_id, $withdrawal_ref_id]);

        $pdo->commit();

        $response = [
            'success' => true,
            'message' => 'Saque solicitado com sucesso e em análise.',
            'amount' => $amount,
            'new_main_balance' => $new_main_balance,
            'new_commission_balance' => $new_commission_balance,
            'withdrawal_ref_id' => $withdrawal_ref_id
        ];
    }
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Para depuração, registre o erro real. Não exiba para o usuário.
    error_log("Erro no processamento do saque para user {$user_id}: " . $e->getMessage());

    http_response_code(500); // Erro interno do servidor
    $response['message'] = 'Erro interno ao processar a solicitação. Tente novamente mais tarde.';
}

echo json_encode($response);
?>