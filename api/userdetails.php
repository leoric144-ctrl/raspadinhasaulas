<?php
// api/me.php
header('Content-Type: application/json; charset=utf-8');
session_start();                  // usa a sessão iniciada no login

require_once __DIR__.'/../db.php';

try{
    if (empty($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error'=>'not authenticated']);
        exit;
    }

    $pdo = db();
    $stmt = $pdo->prepare('SELECT id, name, email, phone, avatar, saldo FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$user){
        http_response_code(404);
        echo json_encode(['error'=>'user not found']);
        exit;
    }

    echo json_encode($user);
}catch(Throwable $e){
    http_response_code(500);
    echo json_encode(['error'=>'server error']);
}
