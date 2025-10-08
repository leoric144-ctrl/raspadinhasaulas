<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *'); // ajuste origem depois
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

require_once __DIR__ . '/../db.php';

// Aumentar o limite de tempo de execução se o gerador de código for muito pesado
// set_time_limit(60);

$data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$name       = trim($data['name']        ?? '');
$email      = strtolower(trim($data['email'] ?? ''));
$phone      = trim($data['phone']       ?? '');
$password   = $data['password']    ?? '';
// ✅ NOVO: Captura o código de indicação, se enviado
$referred_by_code = trim($data['referred_by_code'] ?? '');

// --- Array para coletar erros de validação ---
$validation_errors = [];

if (!$name) {
    $validation_errors['name'] = 'O campo Nome é obrigatório.';
}
if (!$email) {
    $validation_errors['email'] = 'O campo Email é obrigatório.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $validation_errors['email'] = 'Formato de email inválido.';
}
// Phone pode não ser obrigatório em todos os cadastros, mas a validação de formato é importante
if (!empty($phone) && !preg_match('/^\d{10,11}$/', preg_replace('/[^0-9]/', '', $phone))) {
    $validation_errors['phone'] = 'Número de telefone inválido.';
}
if (!$password) {
    $validation_errors['password'] = 'O campo Senha é obrigatório.';
} elseif (strlen($password) < 6) { // Exemplo: senha com mínimo de 6 caracteres
    $validation_errors['password'] = 'A senha deve ter no mínimo 6 caracteres.';
}


if (!empty($validation_errors)) {
    http_response_code(400); // Bad Request para erros de validação de input
    echo json_encode([
        'ok' => false,
        'error' => 'Falha na validação dos campos.',
        'fields' => $validation_errors // Retorna os erros por campo
    ]);
    exit;
}

try {
    $pdo = db();

    // 1. Verificar se o email já existe
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        http_response_code(409); // Conflict
        echo json_encode([
            'ok' => false,
            'error' => 'Email já cadastrado.',
            'fields' => ['email' => 'Este email já está registrado.'] // Erro específico para o campo email
        ]);
        exit;
    }

    // 2. Verificar se o telefone já existe (opcional, se phone é único)
    $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
    if (!empty($cleanPhone)) {
        $stmt_phone = $pdo->prepare('SELECT id FROM users WHERE phone = ?');
        $stmt_phone->execute([$cleanPhone]);
        if ($stmt_phone->fetch()) {
            http_response_code(409); // Conflict
            echo json_encode([
                'ok' => false,
                'error' => 'Telefone já cadastrado.',
                'fields' => ['phone' => 'Este telefone já está registrado.'] // Erro específico para o campo telefone
            ]);
            exit;
        }
    }


    // 3. Lógica para o usuário que indicou (referrer_id)
    $referrer_id = null;
    if (!empty($referred_by_code)) {
        $stmt_referrer = $pdo->prepare('SELECT id FROM users WHERE referral_code = ?');
        $stmt_referrer->execute([$referred_by_code]);
        $referrer_user = $stmt_referrer->fetch(PDO::FETCH_ASSOC);

        if ($referrer_user) {
            $referrer_id = $referrer_user['id'];
        }
        // Se o referred_by_code for inválido, apenas ignora e o usuário não terá um indicador.
        // Você pode adicionar um erro aqui se quiser que códigos inválidos falhem o registro.
        // elseif (empty($referrer_user)) {
        //     http_response_code(400);
        //     echo json_encode([
        //         'ok' => false,
        //         'error' => 'Código de indicação inválido.',
        //         'fields' => ['referred_by_code' => 'O código de indicação é inválido.']
        //     ]);
        //     exit;
        // }
    }

    // 4. Gerar um referral_code único para o NOVO usuário
    $new_user_referral_code = null;
    $max_attempts = 10; // Limite de tentativas para gerar um código único
    for ($i = 0; $i < $max_attempts; $i++) {
        // Gera um código alfanumérico curto (ex: 6 caracteres)
        $potential_code = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);

        $stmt_check_code = $pdo->prepare('SELECT id FROM users WHERE referral_code = ?');
        $stmt_check_code->execute([$potential_code]);

        if (!$stmt_check_code->fetch()) { // Se o código não existe, é único
            $new_user_referral_code = $potential_code;
            break;
        }
    }

    if (is_null($new_user_referral_code)) {
        // Se não conseguiu gerar um código único após várias tentativas
        http_response_code(500); // Internal Server Error
        echo json_encode(['ok' => false, 'error' => 'Não foi possível gerar um código de referência único. Tente novamente.', 'code' => 'REFERRAL_CODE_GEN_FAILED']);
        exit;
    }

    // 5. Inserir o novo usuário com referrer_id e seu próprio referral_code
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare('
        INSERT INTO users (name, email, phone, password_hash, referrer_id, referral_code, commission_rate)
        VALUES (?, ?, ?, ?, ?, ?, ?)
        RETURNING id, created_at, referral_code, commission_rate
    ');
    // Assumindo que 'commission_rate' tem um DEFAULT no DB. Se não, defina um valor aqui.
    // Ex: 0.05 para 5%, 0.1 para 10%
    $default_commission_rate = 0.050; // Este deve ser o mesmo default que você definiu no ALTER TABLE

    $stmt->execute([$name, $email, $cleanPhone, $hash, $referrer_id, $new_user_referral_code, $default_commission_rate]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch como array associativo

    echo json_encode([
        'ok'    => true,
        'user'  => [
            'id'             => $user['id'],
            'name'           => $name,
            'email'          => $email,
            'phone'          => $phone, // Retorna o telefone original formatado se necessário, ou o limpo
            'created_at'     => $user['created_at'],
            'referral_code'  => $user['referral_code'], // Retorna o código gerado
            'commission_rate'=> $user['commission_rate'] // Retorna a taxa definida
        ]
    ]);
    exit; // Importante: Termina a execução após o sucesso

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Erro no servidor', 'detail' => $e->getMessage(), 'code' => 'SERVER_ERROR']);
    exit; // Importante: Termina a execução após o erro
}