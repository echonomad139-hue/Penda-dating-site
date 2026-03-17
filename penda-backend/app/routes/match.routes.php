<?php

use App\Controllers\MatchController;
use App\Middleware\AuthMiddleware;
use App\Middleware\JsonMiddleware;
use App\Middleware\ValidationMiddleware;

$matchController = new MatchController($db);
$authMiddleware = new AuthMiddleware($db);
$jsonMiddleware = new JsonMiddleware($db);
$validationMiddleware = new ValidationMiddleware($db);

// GET /api/matches - list matches for authenticated user
if ($_SERVER['REQUEST_URI'] === '/api/matches' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $user = $authMiddleware->handle(getallheaders());
    $response = $matchController->getMatches($user['id']);
    echo json_encode($response);
}

// POST /api/match/unmatch - remove a match
if ($_SERVER['REQUEST_URI'] === '/api/match/unmatch' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $authMiddleware->handle(getallheaders());
    $data = $jsonMiddleware->handle();
    $validationMiddleware->validateTable($data, 'matches', ['user1_id', 'user2_id']);
    $response = $matchController->unmatch($user['id'], $data['user2_id']);
    echo json_encode($response);
}
