<?php

use App\Controllers\ProfileController;
use App\Middleware\AuthMiddleware;
use App\Middleware\JsonMiddleware;
use App\Middleware\ValidationMiddleware;

$profileController = new ProfileController($db);
$authMiddleware = new AuthMiddleware($db);
$jsonMiddleware = new JsonMiddleware($db);
$validationMiddleware = new ValidationMiddleware($db);

// GET /api/profile
if ($_SERVER['REQUEST_URI'] === '/api/profile' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $user = $authMiddleware->handle(getallheaders());
    $response = $profileController->getProfile($user['id']);
    echo json_encode($response);
}

// PUT /api/profile
if ($_SERVER['REQUEST_URI'] === '/api/profile' && $_SERVER['REQUEST_METHOD'] === 'PUT') {
    $user = $authMiddleware->handle(getallheaders());
    $data = $jsonMiddleware->handle();
    $data['user_id'] = $user['id'];
    $validationMiddleware->validateProfile($data);
    $response = $profileController->updateProfile($data);
    echo json_encode($response);
}

// POST /api/profile/setup
if ($_SERVER['REQUEST_URI'] === '/api/profile/setup' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $user = $authMiddleware->handle(getallheaders());

    $data = $jsonMiddleware->handle();

    $data['user_id'] = $user['id'];

    $response = $profileController->setupProfile($data);

    echo json_encode($response);
}
