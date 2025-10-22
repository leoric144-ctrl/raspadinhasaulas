<?php
// public/api/gerar-pix.php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método não permitido.']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Acesso não autorizado. Você precisa estar logado.']);
    exit;
}

require_once __DIR__ . '/../db.php';

// Constantes para as APIs

// ZEROONEPAY (Token na URL. API_URL é o ENDPOINT base)
$ZEROONEPAY_API_TOKEN = '#';
$ZEROONEPAY_API_URL = 'https://api.zeroonepay.com.br/api'; // Endpoint base
$ZEROONEPAY_OFFER_HASH = '#';
$ZEROONEPAY_PRODUCT_HASH = '#';

// IRONPAY (Token na URL. ENDPOINT é o base)
$IRONPAY_API_TOKEN = '#';
$IRONPAY_ENDPOINT = 'https://api.ironpayapp.com.br/api';
$IRONPAY_OFFER_HASH = '#';
$IRONPAY_PRODUCT_HASH = '#';
$IRONPAY_PRODUCT_ID = null;
$IRONPAY_OFFER_ID = null;


// NOVAS CONSTANTES PARA A PIXUP (Permanece inalterado)
$PIXUP_CLIENT_ID = '#';
$PIXUP_CLIENT_SECRET = '#';
$PIXUP_ENDPOINT = 'https://api.pixupbr.com/v2';
$PIXUP_AUTH_TOKEN = null;

/**
 * Função para obter ou renovar o token da PixUp. (Permanece inalterada)
 * @return string O token de acesso.
 * @throws Exception Se a autenticação falhar.
 */
function getPixUpToken(string $clientId, string $clientSecret): string
{
    // Verifica se já existe um token válido na sessão
    if (isset($_SESSION['pixup_token']) && time() < $_SESSION['pixup_token_expires']) {
        error_log("Usando token PixUp da sessão. Expira em " . ($_SESSION['pixup_token_expires'] - time()) . " segundos.");
        return $_SESSION['pixup_token'];
    }

    // Se não há token válido, faça uma nova requisição
    $url = 'https://api.pixupbr.com/v2/oauth/token';
    $credentials = $clientId . ':' . $clientSecret;
    $base64_credentials = base64_encode($credentials);
    $authorization_header = 'Authorization: Basic ' . $base64_credentials;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        $authorization_header,
        'Content-Type: application/json',
        'Accept: application/json'
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        $responseData = json_decode($response, true) ?? ['error' => 'Erro desconhecido na autenticação.'];
        error_log("Erro ao obter token PixUp: HTTP {$http_code} - " . ($responseData['message'] ?? $responseData['error'] ?? 'Sem mensagem.'));
        throw new Exception("Falha ao autenticar na PixUp: " . ($responseData['message'] ?? $responseData['error'] ?? 'Erro desconhecido.'));
    }

    $responseData = json_decode($response, true);
    if (!isset($responseData['access_token'])) {
        throw new Exception('Token de acesso não encontrado na resposta da PixUp.');
    }

    // Salve o novo token na sessão com o tempo de expiração
    $_SESSION['pixup_token'] = $responseData['access_token'];
    $_SESSION['pixup_token_expires'] = time() + ($responseData['expires_in'] - 60);

    error_log("Token PixUp renovado com sucesso e armazenado na sessão. Expira em " . ($responseData['expires_in'] - 60) . " segundos.");

    return $responseData['access_token'];
}

try {
    $pdo = db();
    $userId = (int)$_SESSION['user_id'];

    $input_data = json_decode(file_get_contents('php://input'), true);
    $amount_in_cents = (int)($input_data['amount'] ?? 0);
    $api_escolhida = $input_data['api'] ?? null;

    if ($amount_in_cents < 50) {
        throw new Exception('O valor mínimo para depósito é R$ 0,50.');
    }

    if (empty($api_escolhida)) {
        throw new Exception('Método de pagamento não selecionado.');
    }

    $stmt = $pdo->prepare("SELECT name, email, phone, document, is_demo FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('Usuário não encontrado.');
    }

    // ✅ VERIFICAÇÃO PARA CONTAS DEMO
    if ($user['is_demo']) {
        http_response_code(403); // Forbidden
        echo json_encode(['ok' => false, 'error' => 'Não é possível realizar depósitos em uma conta de demonstração.']);
        exit;
    }

    $cpf = !empty($user['document']) ? preg_replace('/[^0-9]/', '', $user['document']) : null;
    $phone = !empty($user['phone']) ? preg_replace('/[^0-9]/', '', $user['phone']) : null;
    $amountInReais = $amount_in_cents / 100.0;

    $pdo->beginTransaction();

    $stmt_insert_tx = $pdo->prepare(
        "INSERT INTO transactions (user_id, amount, status, provider, type) VALUES (?, ?, ?, ?, ?) RETURNING id"
    );
    $stmt_insert_tx->execute([
        $userId, $amountInReais, 'PENDING',
        $api_escolhida, 'deposit'
    ]);
    $localTransactionId = $stmt_insert_tx->fetchColumn();
    if (!$localTransactionId) throw new Exception('Falha ao registrar a transação local.');

    error_log("gerar-pix.php: DEBUG - Transação PENDING registrada com ID local: {$localTransactionId}.");

    $response_curl = null;
    $http_status = null;
    $responseData = [];

    // =======================================================
    // LÓGICA UNIFICADA: IRONPAY E ZEROONEPAY
    // Token agora SEMPRE vai na URL como api_token, replicando o teste de sucesso.
    // =======================================================
    if (strtolower($api_escolhida) === 'ironpay' || strtolower($api_escolhida) === 'zeroonepay') {

        $is_ironpay = strtolower($api_escolhida) === 'ironpay';

        // Determina o Endpoint base e o Token
        $api_base_endpoint = $is_ironpay ? $IRONPAY_ENDPOINT : $ZEROONEPAY_API_URL;
        $api_token = $is_ironpay ? $IRONPAY_API_TOKEN : $ZEROONEPAY_API_TOKEN;
        $offer_hash = $is_ironpay ? $IRONPAY_OFFER_HASH : $ZEROONEPAY_OFFER_HASH;
        $product_hash = $is_ironpay ? $IRONPAY_PRODUCT_HASH : $ZEROONEPAY_PRODUCT_HASH;
        $provider_name = $is_ironpay ? 'IronPay' : 'ZeroOnePay';

        // O webhook URL pode ser o mesmo ou diferente dependendo da configuração do painel
        $webhook_url = "https://{$_SERVER['HTTP_HOST']}/api/webhook-{$provider_name}.php?local_id={$localTransactionId}";


        // 1. MONTAGEM DA URL (Token SEMPRE na URL como 'api_token')
        $api_url_base_route = "{$api_base_endpoint}/public/v1/transactions";
        $api_url_full = $api_url_base_route . "?api_token={$api_token}";

        // 2. MONTAGEM DOS HEADERS (Apenas Content-Type e Accept, sem Authorization)
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        // 3. MONTAGEM DO PAYLOAD (Unificado no formato, com hashes específicas)
        $payload = json_encode([
            'amount' => $amount_in_cents,
            'offer_hash' => $offer_hash,
            'payment_method' => 'pix',
            'customer' => [
                'name' => $user['name'] ?? 'Usuário Sem Nome',
                'email' => $user['email'] ?? 'sem_email@example.com',
                'phone_number' => $phone,
                'document' => $cpf,
            ],
            'cart' => [
                [
                    'product_hash' => $product_hash,
                    'title' => 'Recarga de Saldo - Raspadinha',
                    'price' => $amount_in_cents,
                    'quantity' => 1,
                    'operation_type' => 1,
                    'tangible' => false,
                    'product_id' => $IRONPAY_PRODUCT_ID,
                    'offer_id' => $IRONPAY_OFFER_ID,
                ]
            ],
            'installments' => 1,
            'expire_in_days' => 1,
            'transaction_origin' => 'api',
            'postback_url' => $webhook_url
        ]);

        // 4. Configuração e Execução do cURL (UNIFICADO)
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url_full);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 25);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response_curl = curl_exec($ch);

        if ($response_curl === false) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            throw new Exception("O gateway de pagamento ({$provider_name}) demorou para responder: " . $error_msg);
        }

        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $responseData = json_decode($response_curl, true);


    // LÓGICA PIXUP (PERMANECE INALTERADA)
    } elseif (strtolower($api_escolhida) === 'pixup') {
        // Obtenha o token, renovando se necessário
        $PIXUP_AUTH_TOKEN = getPixUpToken($PIXUP_CLIENT_ID, $PIXUP_CLIENT_SECRET);

        $api_url_full = "{$PIXUP_ENDPOINT}/pix/qrcode";

        $payload = json_encode([
            'amount' => $amountInReais,
            'external_id' => (string)$localTransactionId,
            'postbackUrl' => "https://raspadinhasaulas.onrender.com/api/webhook-pixup.php", //alterar essa url para a sua url real
            'payerQuestion' => 'Recarga de saldo na Raspa Green',
            'payer' => [
                'name' => $user['name'] ?? 'Usuário Sem Nome',
                'document' => $cpf,
                'email' => $user['email'] ?? 'sem_email@example.com'
            ]
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url_full);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        $headers = [
            "Authorization: Bearer {$PIXUP_AUTH_TOKEN}",
            "accept: application/json",
            "content-type: application/json"
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response_curl = curl_exec($ch);
        if ($response_curl === false) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            throw new Exception("O gateway de pagamento (PixUp) demorou para responder: " . $error_msg);
        }
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $responseData = json_decode($response_curl, true);
    }

    error_log("gerar-pix.php: DEBUG - Resposta da API {$api_escolhida} (HTTP {$http_status}): " . ($response_curl ?: 'Vazio'));

    $providerTransactionId = null; $pixQrCodeBase64 = null; $pixCopyPasteCode = null; $providerInternalHash = null; $pollingId = null;

    // PROCESSAMENTO DA RESPOSTA (Mantido para lidar com as diferenças de resposta)
    if (strtolower($api_escolhida) === 'ironpay') {
        $providerName = 'IronPay';
        if ($http_status >= 400) throw new Exception($responseData['message'] ?? "Erro desconhecido ao gerar o PIX na {$providerName}.");
        // IronPay usa 'transaction' e 'hash'
        if (!isset($responseData['transaction'], $responseData['pix']['pix_qr_code'], $responseData['hash'])) throw new Exception("Resposta da API {$providerName} incompleta.");

        $providerTransactionId = $responseData['transaction'];
        $pixCopyPasteCode = $responseData['pix']['pix_qr_code'];
        $providerInternalHash = $responseData['hash'];
        $pollingId = $providerInternalHash;

    } elseif (strtolower($api_escolhida) === 'zeroonepay') {
        $providerName = 'ZeroOnePay';
        if ($http_status >= 400) throw new Exception($responseData['message'] ?? "Erro desconhecido ao gerar o PIX na {$providerName}.");

        // Assumimos que o ZeroOnePay AGORA responde no formato da IronPay
        if (!isset($responseData['transaction'], $responseData['pix']['pix_qr_code'], $responseData['hash'])) {
            // Se falhar, tenta o formato antigo/alternativo (mantendo a compatibilidade temporária)
            if (isset($responseData['id'], $responseData['pixCode'])) {
                $providerTransactionId = $responseData['id'];
                $pixQrCodeBase64 = $responseData['pixQrCode'] ?? null;
                $pixCopyPasteCode = $responseData['pixCode'];
                $pollingId = $providerTransactionId;
            } else {
                 throw new Exception("Resposta da API {$providerName} incompleta ou formato desconhecido.");
            }
        } else {
            // Resposta no formato IronPay (se o endpoint estiver totalmente unificado)
            $providerTransactionId = $responseData['transaction'];
            $pixCopyPasteCode = $responseData['pix']['pix_qr_code'];
            $providerInternalHash = $responseData['hash'];
            $pollingId = $providerInternalHash;
        }

    } elseif (strtolower($api_escolhida) === 'pixup') {
        $providerName = 'PixUp';
        if ($http_status >= 400 || !isset($responseData['qrcode'])) {
            throw new Exception($responseData['message'] ?? "Erro desconhecido ao gerar o PIX na {$providerName}.");
        }

        $providerTransactionId = (string)$localTransactionId;
        $pixQrCodeBase64 = null;
        $pixCopyPasteCode = $responseData['qrcode'];
        $pollingId = (string)$localTransactionId;
    }

    $stmt_update_tx = $pdo->prepare("UPDATE transactions SET provider_transaction_id = ?, pix_code = ?, provider_hash = ? WHERE id = ?");
    $stmt_update_tx->execute([$providerTransactionId, $pixCopyPasteCode, $providerInternalHash, $localTransactionId]);

    $pdo->commit();

    http_response_code(200);
    echo json_encode([
        'ok' => true,
        'transactionId' => $pollingId,
        'qr_code_base64' => $pixQrCodeBase64,
        'pix_copy_paste_code' => $pixCopyPasteCode
    ]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("gerar-pix.php: ERRO CATCH FINAL - Mensagem: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
