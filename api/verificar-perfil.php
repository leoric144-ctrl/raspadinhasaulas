<?php
// api/verificar-perfil.php
declare(strict_types=1);

// NÃO DEVE HAVER NENHUM ESPAÇO, QUEBRA DE LINHA OU CARACTERE ANTES DESTA TAG <?php

header('Content-Type: application/json; charset=utf-8');
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.']);
    exit; // Importante: Garante que o script pare aqu
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Método não permitido. Utilize GET.']);
    exit; // Importante: Garante que o script pare aqui
}

require_once __DIR__ . '/../db.php'; // Ajuste este caminho se necessário.

$userId = $_SESSION['user_id'];

try {
    $pdo = db(); // Sua função para obter a conexão PDO

    $stmt = $pdo->prepare("SELECT document FROM users WHERE id = :user_id LIMIT 1");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && !empty($user['document'])) {
        http_response_code(200);
        echo json_encode(['success' => true, 'profile_complete' => true, 'message' => 'Perfil completo.']);
        exit; // Adicionado: Garante que o script pare após enviar o JSON de sucesso
    } else {
        http_response_code(200);
        echo json_encode(['success' => true, 'profile_complete' => false, 'message' => 'Seu perfil está incompleto. O CPF é obrigatório para depositar.']);
        exit; // Adicionado: Garante que o script pare após enviar o JSON de perfil incompleto
    }

} catch (PDOException $e) {
    http_response_code(500);
    error_log("Erro no banco de dados ao verificar perfil: " . $e->getMessage()); // Loga o erro para sua análise
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor ao verificar perfil.']);
    exit; // Adicionado: Garante que o script pare após enviar o JSON de erro DB
} catch (Exception $e) {
    http_response_code(500);
    error_log("Erro geral ao verificar perfil: " . $e->getMessage()); // Loga o erro para sua análise
    echo json_encode(['success' => false, 'message' => 'Ocorreu um erro inesperado.']);
    exit; // Adicionado: Garante que o script pare após enviar o JSON de erro geral
}

// NENHUMA TAG DE FECHAMENTO PHP `?>` DEVE EXISTIR AQUI NO FINAL DO ARQUIVO.