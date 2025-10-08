<?php
// /api/buy-game.php

declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
session_start();

// --- Rate Limiting ---
const MIN_SECONDS_BETWEEN_BETS = 2;
if (isset($_SESSION['last_bet_time']) && (time() - $_SESSION['last_bet_time'] < MIN_SECONDS_BETWEEN_BETS)) {
    http_response_code(429);
    echo json_encode(['error' => 'Por favor, aguarde antes de apostar novamente.', 'code' => 'RATE_LIMIT']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido.', 'code' => 'METHOD_NOT_ALLOWED']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Acesso não autorizado.', 'code' => 'UNAUTHORIZED']);
    exit;
}

$post_data = json_decode(file_get_contents('php://input'), true);
$game_name = $post_data['game_name'] ?? null;

if (empty($game_name)) {
    http_response_code(400);
    echo json_encode(['error' => 'Nome do jogo não especificado.', 'code' => 'GAME_NAME_MISSING']);
    exit;
}

require_once __DIR__ . '/../db.php';

try {
    $pdo = db();
    $userId = (int)$_SESSION['user_id'];
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT bet_cost, prizes_json, win_chance_percent FROM games WHERE name = ?");
    $stmt->execute([$game_name]);
    $game_settings = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$game_settings) {
        throw new Exception('Jogo não encontrado.', 404);
    }

    $bet_cost = (float)$game_settings['bet_cost'];
    $win_chance_percent_normal = (float)$game_settings['win_chance_percent'];
    $prizes = json_decode($game_settings['prizes_json'], true);
    $round_id = uniqid('game_', true); // Definindo round_id aqui

    // <-- ALTERAÇÃO AQUI: Adicionado 'rollover_amount' na busca de dados do usuário
    $stmt = $pdo->prepare("SELECT saldo, rollover_amount, is_demo, demo_win_rate FROM users WHERE id = ? FOR UPDATE");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('Usuário não encontrado.', 404);
    }

    $is_demo = (bool)$user['is_demo'];

    if ((float)$user['saldo'] < $bet_cost) {
        // Garantir rollback em caso de falha antes do commit
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        http_response_code(400);
        echo json_encode(['error' => 'Saldo insuficiente para esta aposta.', 'code' => 'INSUFFICIENT_BALANCE']);
        exit;
    }

    // 1. Debita a aposta do saldo principal
    $stmt_debit = $pdo->prepare("UPDATE users SET saldo = saldo - ? WHERE id = ?");
    $stmt_debit->execute([$bet_cost, $userId]);
    $_SESSION['last_bet_time'] = time();

    // <-- ALTERAÇÃO AQUI: Lógica para abater o valor da aposta do rollover
    if (!$is_demo && (float)$user['rollover_amount'] > 0) {
        $stmt_rollover = $pdo->prepare("UPDATE users SET rollover_amount = GREATEST(0, rollover_amount - ?) WHERE id = ?");
        $stmt_rollover->execute([$bet_cost, $userId]);
    }

    // 2. Lógica de Faturamento e Bônus
    $winningPrize = null;
    $action = '';
    $prizeAmountForHistory = 0.00;
    $user_wins = false;

    // Lógica para contas demo
    if ($is_demo) {
        $win_chance_percent = (float)$user['demo_win_rate'];
        if (random_int(1, 100) <= $win_chance_percent) {
            $user_wins = true;
            $winningPrize = $prizes[array_rand($prizes)];
        }
    } else { // Lógica para contas reais
        $stmt_faturamento = $pdo->prepare("SELECT * FROM bonus_system WHERE game_name = ? FOR UPDATE");
        $stmt_faturamento->execute([$game_name]);
        $bonus_system = $stmt_faturamento->fetch(PDO::FETCH_ASSOC);

        if ($bonus_system) {
            $current_faturamento = (float)$bonus_system['current_faturamento'];
            $faturamento_meta = (float)$bonus_system['faturamento_meta'];
            $bonus_amount = (float)$bonus_system['bonus_amount'];
            $current_bonus_paid = (float)$bonus_system['current_bonus_paid'];
            $bonus_remaining = $bonus_amount - $current_bonus_paid;

            // Se a meta foi batida, ative o bônus para que o sistema comece a pagar.
            if ($current_faturamento >= $faturamento_meta && !$bonus_system['is_bonus_active']) {
                $stmt_activate_bonus = $pdo->prepare("UPDATE bonus_system SET is_bonus_active = TRUE WHERE game_name = ?");
                $stmt_activate_bonus->execute([$game_name]);
                $bonus_system['is_bonus_active'] = true;
            }

            // Lógica para pagar prêmios se o bônus estiver ativo
            if ($bonus_system['is_bonus_active'] && $bonus_remaining > 0) {
                // Filtra os prêmios que são menores ou iguais ao valor restante do bônus
                $prizes_validos = array_filter($prizes, function($prize) use ($bonus_remaining) {
                    return isset($prize['amount']) && $prize['amount'] > 0 && $prize['amount'] <= $bonus_remaining;
                });

                if (!empty($prizes_validos)) {
                    // Escolhe um prêmio válido aleatoriamente
                    $winningPrize = $prizes_validos[array_rand($prizes_validos)];
                    $user_wins = true;
                } else {
                    // Bônus ativo mas sem prêmios válidos para pagar. Desativa o bônus.
                    $stmt_deactivate_bonus = $pdo->prepare("UPDATE bonus_system SET is_bonus_active = FALSE WHERE game_name = ?");
                    $stmt_deactivate_bonus->execute([$game_name]);
                }
            } else {
                // Se o bônus não está ativo ou já foi totalmente pago, o usuário perde.
                $user_wins = false;
            }
        }
    }

    if ($user_wins && $winningPrize) {
        $finalPrize = $winningPrize;
        $action = 'Ganhou';
        $prizeAmountForHistory = (float)($finalPrize['amount'] ?? 0.00);

        $stmt_credit = $pdo->prepare("UPDATE users SET saldo = saldo + ? WHERE id = ?");
        $stmt_credit->execute([$prizeAmountForHistory, $userId]);
    } else {
        $action = 'Perdeu';
        $finalPrize = null;
        $prizeAmountForHistory = 0.00;
    }

    // Preenche o grid com base no resultado final
    if ($user_wins) {
        $grid = array_fill(0, 3, ['name' => $finalPrize['name'], 'image' => $finalPrize['image']]);
        $other_prizes = array_values(array_filter($prizes, function($p) use ($finalPrize) {
            return $p['name'] !== $finalPrize['name'];
        }));
        $temp_grid_fill = [];
        while (count($temp_grid_fill) < 6 && !empty($other_prizes)) {
            $random_prize_item = $other_prizes[array_rand($other_prizes)];
            $temp_grid_fill[] = ['name' => $random_prize_item['name'], 'image' => $random_prize_item['image']];
        }
        $grid = array_merge($grid, $temp_grid_fill);
    } else {
        $counts = [];
        $temp_prizes_fill = [];
        while (count($temp_prizes_fill) < 9) {
            $random_prize_item = $prizes[array_rand($prizes)];
            $prizeName = $random_prize_item['name'];
            if (($counts[$prizeName] ?? 0) < 2) {
                $temp_prizes_fill[] = ['name' => $random_prize_item['name'], 'image' => $random_prize_item['image']];
                $counts[$prizeName] = ($counts[$prizeName] ?? 0) + 1;
            }
        }
        $grid = $temp_prizes_fill;
    }

    shuffle($grid);

    // Salva o histórico e atualiza os valores do sistema de bônus
    $stmt_historic = $pdo->prepare(
        "INSERT INTO historicplay (user_id, game_name, bet_amount, action, prize_amount, round_id)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt_historic->execute([$userId, $game_name, $bet_cost, $action, $prizeAmountForHistory, $round_id]);

    if (!$is_demo && isset($bonus_system) && $bonus_system) {
        $stmt_update_faturamento = $pdo->prepare("UPDATE bonus_system SET current_faturamento = current_faturamento + ? WHERE game_name = ?");
        $stmt_update_faturamento->execute([$bet_cost, $game_name]);

        if ($user_wins && $winningPrize) {
            $stmt_update_bonus_paid = $pdo->prepare("UPDATE bonus_system SET current_bonus_paid = current_bonus_paid + ? WHERE game_name = ?");
            $stmt_update_bonus_paid->execute([$prizeAmountForHistory, $game_name]);
        }

        // Verifica se o bônus foi totalmente pago e desativa-o
        $stmt_check_bonus = $pdo->prepare("SELECT current_bonus_paid, bonus_amount FROM bonus_system WHERE game_name = ?");
        $stmt_check_bonus->execute([$game_name]);
        $updated_bonus_system = $stmt_check_bonus->fetch(PDO::FETCH_ASSOC);

        if ($updated_bonus_system && (float)$updated_bonus_system['current_bonus_paid'] >= (float)$updated_bonus_system['bonus_amount']) {
            $stmt_final_deactivate = $pdo->prepare("UPDATE bonus_system SET is_bonus_active = FALSE WHERE game_name = ?");
            $stmt_final_deactivate->execute([$game_name]);
        }
    }

    $stmt = $pdo->prepare("SELECT saldo FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $newBalance = (float)($stmt->fetchColumn() ?: 0.0);

    $pdo->commit();

    $response = [
        "message" => "Success",
        "data" => [
            "scratch" => [ "id" => $round_id, "name" => $game_name, "banner" => "...", "grid_size" => 3 ],
            "grid" => $grid,
            "prize" => $finalPrize ? [
                'name' => $finalPrize['name'],
                'amount' => (float)($finalPrize['amount'] ?? 0.0),
                'image' => $finalPrize['image']
            ] : null,
            "isDemo" => $is_demo
        ],
        "newBalance" => $newBalance
    ];

    http_response_code(200);
    echo json_encode($response);

} catch (Exception $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    http_response_code(400);
    $error_code = 'GENERIC_ERROR';
    if ($e->getMessage() === 'Saldo insuficiente para esta aposta.') {
        $error_code = 'INSUFFICIENT_BALANCE';
    }
    echo json_encode(['error' => $e->getMessage(), 'code' => $error_code]);
}