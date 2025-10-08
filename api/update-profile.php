<?php
// api/update-profile.php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Acesso não autorizado.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método não permitido.']);
    exit;
}

require_once __DIR__ . '/../db.php';

// Função de validação de CPF (mantida)
function is_cpf_valid(string $cpf): bool {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }
    return true;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $field = $data['field'] ?? null;
    $value = trim($data['value'] ?? '');
    $userId = $_SESSION['user_id'];
    
    $allowedFields = ['email', 'username', 'phone', 'document'];
    if (!in_array($field, $allowedFields)) {
        throw new Exception('Campo inválido para atualização.');
    }

    $pdo = db();
    $valueToSave = $value;

    switch ($field) {
        case 'email':
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Formato de email inválido.');
            }
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$value, $userId]);
            if ($stmt->fetch()) {
                throw new Exception('Este email já está em uso por outra conta.');
            }
            break;

        case 'username':
            if (strlen($value) < 3 || strlen($value) > 50) {
                throw new Exception('Username deve ter entre 3 e 50 caracteres.');
            }
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $value)) {
                throw new Exception('Username pode conter apenas letras, números e underscore (_).');
            }
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->execute([$value, $userId]);
            if ($stmt->fetch()) {
                throw new Exception('Este username já está em uso.');
            }
            break;
        
        // NOVO: Verificação de duplicidade para o telefone
        case 'phone':
            $cleanPhone = preg_replace('/[^0-9]/', '', $value);
            if (strlen($cleanPhone) < 10) {
                throw new Exception('Número de telefone inválido.');
            }
            $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ? AND id != ?");
            $stmt->execute([$cleanPhone, $userId]);
            if ($stmt->fetch()) {
                throw new Exception('Este telefone já está em uso por outra conta.');
            }
            $valueToSave = $cleanPhone;
            break;
        
        // AJUSTE: Adicionada verificação de duplicidade para o documento
        case 'document':
            $cleanCPF = preg_replace('/[^0-9]/', '', $value);
            if (!is_cpf_valid($cleanCPF)) {
                throw new Exception('O CPF informado não é válido.');
            }
            $stmt = $pdo->prepare("SELECT id FROM users WHERE document = ? AND id != ?");
            $stmt->execute([$cleanCPF, $userId]);
            if ($stmt->fetch()) {
                throw new Exception('Este CPF já está em uso por outra conta.');
            }
            $valueToSave = $cleanCPF;
            break;
    }

    $sql = "UPDATE users SET $field = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$valueToSave, $userId]);

    $response = [
        'ok' => true,
        'message' => ucfirst($field) . ' atualizado com sucesso!',
        'newValue' => htmlspecialchars($value)
    ];

    // Formata o valor de volta para exibição (CPF e Telefone)
    if ($field === 'document') {
        $response['formattedValue'] = vsprintf('%s.%s.%s-%s', str_split($valueToSave, 3));
    }
    if ($field === 'phone') {
        if (strlen($valueToSave) == 11) {
            $response['formattedValue'] = vsprintf('(%s) %s-%s', [substr($valueToSave, 0, 2), substr($valueToSave, 2, 5), substr($valueToSave, 7, 4)]);
        } else {
            $response['formattedValue'] = vsprintf('(%s) %s-%s', [substr($valueToSave, 0, 2), substr($valueToSave, 2, 4), substr($valueToSave, 6, 4)]);
        }
    }

    http_response_code(200);
    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
?>