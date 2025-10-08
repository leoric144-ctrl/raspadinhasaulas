<?php
// public/api/get_user_withdraw_data.php

// Inicia a sessão para acessar $_SESSION['user_id']
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Caminho para o arquivo db.php na raiz do projeto
require_once __DIR__ . '/../db.php';

// Definir o fuso horário para São Paulo (GMT-3)
date_default_timezone_set('America/Sao_Paulo');

$response = [
    'success' => false,
    'message' => 'Erro ao carregar dados do usuário.',
    'document_raw' => '',
    'email' => '',
    'phone_raw' => '',
    'main_balance_cents' => 0,
    'commission_balance_cents' => 0,
    'rollover_cents' => 0 // NOVO: Chave de resposta para o rollover
];

$logged_in_user_id = $_SESSION['user_id'] ?? null;

if (!$logged_in_user_id) {
    http_response_code(401);
    $response['message'] = 'Usuário não autenticado.';
    echo json_encode($response);
    exit;
}

try {
    $pdo = db();

    // ALTERADO: Adicionando 'rollover_amount' à consulta
    $stmt = $pdo->prepare("SELECT document, email, phone, saldo, commission_balance, rollover_amount FROM users WHERE id = ?");
    $stmt->execute([$logged_in_user_id]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user_data) {
        $response['success'] = true;
        $response['message'] = 'Dados do usuário carregados com sucesso.';
        $response['document_raw'] = preg_replace("/[^0-9]/", "", $user_data['document'] ?? '');
        $response['email'] = $user_data['email'] ?? '';
        $response['phone_raw'] = preg_replace("/[^0-9]/", "", $user_data['phone'] ?? '');

        // Retornando os saldos e o rollover em centavos
        $response['main_balance_cents'] = floatval($user_data['saldo'] ?? 0) * 100;
        $response['commission_balance_cents'] = floatval($user_data['commission_balance'] ?? 0) * 100;
        $response['rollover_cents'] = floatval($user_data['rollover_amount'] ?? 0) * 100; // NOVO: Preenchendo o valor do rollover

    } else {
        $response['message'] = 'Dados do usuário não encontrados.';
    }
} catch (PDOException $e) {
    error_log("Erro no get_user_withdraw_data.php: " . $e->getMessage());
    $response['message'] = 'Erro interno do servidor ao carregar dados.';
}

echo json_encode($response);