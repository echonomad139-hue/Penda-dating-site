<?php

use App\Controllers\MessageController;
use App\Middleware\AuthMiddleware;
use App\Middleware\JsonMiddleware;
use App\Middleware\ValidationMiddleware;

$messageController = new MessageController($db);
$authMiddleware = new AuthMiddleware($db);
$jsonMiddleware = new JsonMiddleware($db);
$validationMiddleware = new ValidationMiddleware($db);

// GET /api/messages/{match_id} - fetch messages for a match
if (preg_match('#^/api/messages/(\d+)$#', $_SERVER['REQUEST_URI'], $matches) && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $user = $authMiddleware->handle(getallheaders());
    $matchId = $matches[1];
    $response = $messageController->getMessages($user['id'], $matchId);
    echo json_encode($response);
}

// POST /api/messages - send a message
if ($_SERVER['REQUEST_URI'] === '/api/messages' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $authMiddleware->handle(getallheaders());
    $data = $jsonMiddleware->handle();
    $data['sender_id'] = $user['id'];
    $validationMiddleware->validateMessage($data);
    $response = $messageController->sendMessage($data);
    echo json_encode($response);
}
