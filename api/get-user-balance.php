<?php
// /api/get-user-balance.php

// 1. Configuração Padrão
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
session_start();

// 2. Segurança: Apenas método GET é permitido
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Método não permitido.']);
    exit;
}

// 3. Segurança: Verifica se o usuário está autenticado (logado)
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['error' => 'Acesso não autorizado.']);
    exit;
}

// Inclui a conexão com o banco de dados
require_once __DIR__ . '/../db.php';

try {
    $pdo = db();
    $userId = (int)$_SESSION['user_id'];

    // 4. Lógica Principal: Busca o saldo do usuário logado
    $stmt = $pdo->prepare("SELECT saldo FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // 5. Resposta de Sucesso
        // Converte o saldo para float para garantir que seja um número no JSON
        $balance = (float)$user['saldo'];
        
        http_response_code(200);
        echo json_encode(['balance' => $balance]);
    } else {
        // Caso raro onde o ID da sessão existe mas o usuário foi deletado do banco
        http_response_code(404); // Not Found
        echo json_encode(['error' => 'Usuário não encontrado.']);
    }

} catch (Exception $e) {
    // 6. Tratamento de Erro Genérico
    // Em um ambiente de produção, é bom logar o erro real em um arquivo. Ex: error_log($e->getMessage());
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Ocorreu um erro no servidor ao buscar o saldo.']);
}
?>