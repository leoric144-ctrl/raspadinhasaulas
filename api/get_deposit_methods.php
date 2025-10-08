<?php
// api/get_deposit_methods.php
// Este endpoint retorna uma lista de todos os métodos de depósito que estão ATIVOS.

declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
session_start();

// 1. Verificação de Autenticação: Garante que apenas usuários logados possam ver os métodos.
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['ok' => false, 'error' => 'Usuário não autenticado.']);
    exit;
}

// 2. Conexão com o Banco de Dados
// O caminho __DIR__ . '/../db.php' assume que este arquivo está na pasta 'api' e o 'db.php' na raiz.
require_once __DIR__ . '/../db.php';
try {
    $pdo = db();

    // 3. Consulta ao Banco de Dados
    // A consulta busca todos os métodos que estão marcados como ativos (is_active = TRUE).
    // Se você desativar um método no seu painel de admin, ele não será retornado por esta consulta.
    $stmt = $pdo->query("
        SELECT id, name, provider_key, icon_url, description
        FROM payment_methods
        WHERE is_active = TRUE
        ORDER BY id
    ");

    $methods = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Resposta de Sucesso
    // Retorna um objeto JSON no formato que o nosso JavaScript espera.
    echo json_encode(['ok' => true, 'methods' => $methods]);

} catch (Exception $e) {
    // 5. Tratamento de Erros
    // Se algo der errado com a conexão ou a consulta, um erro genérico é retornado.
    error_log("Erro em get_deposit_methods.php: " . $e->getMessage()); // Log para sua depuração

    http_response_code(500); // Internal Server Error
    echo json_encode(['ok' => false, 'error' => 'Não foi possível carregar os métodos de pagamento.']);
}