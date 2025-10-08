<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
session_start();

// 1. Autenticação: Garante que apenas usuários logados podem acessar.
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['ok' => false, 'error' => 'Acesso não autorizado.']);
    exit;
}

// 2. Método HTTP: Permite apenas requisições POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['ok' => false, 'error' => 'Método não permitido.']);
    exit;
}

// Inclui a conexão com o banco de dados
require_once __DIR__ . '/../db.php';

try {
    $pdo = db();
    $userId = (int)$_SESSION['user_id'];

    // 3. Verificação: Checa se o usuário já não possui um código.
    $stmt = $pdo->prepare("SELECT referral_code FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!empty($user['referral_code'])) {
        throw new Exception('Você já possui um código de afiliação.');
    }

    // 4. Geração de Código Único
    $newCode = null;
    $isCodeUnique = false;
    do {
        // Gera um código alfanumérico de 6 caracteres (ex: A4F1B9)
        $newCode = strtoupper(bin2hex(random_bytes(3)));

        // Verifica se o código gerado já existe no banco
        $stmt = $pdo->prepare("SELECT id FROM users WHERE referral_code = ?");
        $stmt->execute([$newCode]);
        
        // Se não encontrar nenhum registro, o código é único
        if ($stmt->fetch() === false) {
            $isCodeUnique = true;
        }
    } while (!$isCodeUnique);

    // 5. Atualização no Banco de Dados
    $sql = "UPDATE users SET referral_code = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$newCode, $userId]);

    // 6. Resposta de Sucesso
    http_response_code(200); // OK
    echo json_encode([
        'ok' => true,
        'message' => 'Seu código de afiliação foi criado com sucesso!',
        'referral_code' => $newCode,
        'referral_link' => 'https://raspagreen.com/r/' . $newCode // Exemplo de link
    ]);

} catch (PDOException $e) {
    // Erro de banco de dados
    http_response_code(500); // Internal Server Error
    echo json_encode(['ok' => false, 'error' => 'Erro no servidor. Tente novamente mais tarde.']);
    // Em modo de desenvolvimento, você poderia logar o erro: error_log($e->getMessage());
} catch (Exception $e) {
    // Outros erros (usuário já tem código, etc)
    http_response_code(400); // Bad Request
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
?>