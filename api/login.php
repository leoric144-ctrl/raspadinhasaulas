<?php
// api/login.php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

// CORS (ajuste os domínios se necessário)
$allowed = ['http://localhost', 'http://localhost:8000', 'https://raspadinhas-b008f8ce1070.herokuapp.com'];
$origin  = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed, true)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Credentials: true');
}
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

session_start();
require_once __DIR__ . '/../db.php'; // Certifique-se de que este caminho para 'db.php' está correto,
                                    // assumindo que ele inicializa e retorna o objeto $pdo.
                                    // Se 'db.php' apenas define uma função `db()`, mantenha `db()` e `$pdo = db();`

try {
    $raw   = file_get_contents('php://input');
    $data = $raw ? json_decode($raw, true) : $_POST;

    $email     = strtolower(trim($data['email']     ?? ''));
    $password =             ($data['password'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Email e senha são obrigatórios']);
        exit;
    }

    // Conecta ao banco de dados usando a função/método fornecido por db.php
    $pdo = db(); // Se db.php retorna o PDO diretamente, pode ser $pdo = require_once __DIR__ . '/../db.php';

    // MODIFICAÇÃO AQUI: Adicionado 'is_blocked' à seleção
    $stmt = $pdo->prepare('SELECT id, name, email, phone, password_hash, created_at, is_blocked
                             FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 1. Verifica se o usuário existe e a senha está correta
    if (!$user || !password_verify($password, $user['password_hash'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Credenciais inválidas']);
        exit;
    }

    // MODIFICAÇÃO AQUI: Nova verificação de status de bloqueio
    if ($user['is_blocked']) {
        http_response_code(403); // 403 Forbidden - Ação proibida
        echo json_encode(['error' => 'Sua conta está bloqueada. Entre em contato com o suporte.']);
        exit;
    }

    // Se as credenciais estiverem corretas e o usuário não estiver bloqueado, prossiga com o login.
    // =================================================================
    // AJUSTE: INÍCIO DA LÓGICA DE CRIAÇÃO DO TOKEN E COOKIE
    // =================================================================

    // 1. Gera um token de autenticação seguro e único.
    $token = bin2hex(random_bytes(32));

    // 2. Define as opções de segurança e duração do cookie.
    $cookieOptions = [
        'expires' => time() + (86400 * 30), // Expira em 30 dias
        'path' => '/',
        'secure' => true,    // FUNDAMENTAL: Mude para 'true' em produção com HTTPS.
        'httponly' => true, // Impede que o cookie seja acessado por JavaScript (mais seguro).
        'samesite' => 'Lax' // Proteção contra ataques CSRF.
    ];

    // 3. Define o cookie 'auth_token' no navegador do usuário.
    setcookie('auth_token', $token, $cookieOptions);

    // =================================================================
    // AJUSTE: FIM DA LÓGICA
    // =================================================================

    unset($user['password_hash']);
    unset($user['is_blocked']); // Remova também o status de bloqueio da resposta para o cliente
    $_SESSION['user_id'] = $user['id'];

    // Retorna o token também no JSON para o JavaScript poder usar
    echo json_encode([
        'ok'      => true,
        'user'    => $user,
        'token'   => $token, // Adicionado o token na resposta
        'redirect' => '/inicio.php'
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error'  => 'Erro no servidor',
        'detail' => $e->getMessage() // remova esta linha em produção
    ]);
}