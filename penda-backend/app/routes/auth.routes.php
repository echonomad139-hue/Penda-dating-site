<?php

use App\Controllers\AuthController;
use App\Middleware\AuthMiddleware;
use App\Middleware\JsonMiddleware;
use App\Middleware\ValidationMiddleware;

$authController = new AuthController($db);
$authMiddleware = new AuthMiddleware($db);
$jsonMiddleware = new JsonMiddleware($db);
$validationMiddleware = new ValidationMiddleware($db);

// --------------------------------------------------
// POST /api/register
// --------------------------------------------------
if ($_SERVER['REQUEST_URI'] === '/api/register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = $jsonMiddleware->handle();

        // Validate request
        $validationMiddleware->validateRegister($data);

        // Call controller
        $response = $authController->register($data);
    } catch (\Exception $e) {
        $response = [
            'status' => false,
            'message' => 'Registration failed',
            'error' => $e->getMessage()
        ];
    }

    echo json_encode($response);
    exit;
}

// --------------------------------------------------
// POST /api/login
// --------------------------------------------------
if ($_SERVER['REQUEST_URI'] === '/api/login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = $jsonMiddleware->handle();

        // You could optionally validate fields here
        if (empty($data['phone']) || empty($data['password'])) {
            throw new \Exception("Phone and password are required");
        }

        $response = $authController->login($data);
    } catch (\Exception $e) {
        $response = [
            'status' => false,
            'message' => 'Login failed',
            'error' => $e->getMessage()
        ];
    }

    echo json_encode($response);
    exit;
}

// --------------------------------------------------
// POST /api/refresh
// --------------------------------------------------
if ($_SERVER['REQUEST_URI'] === '/api/refresh' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = $jsonMiddleware->handle();

        if (empty($data['refresh_token'])) {
            throw new \Exception("Refresh token is required");
        }

        $response = $authController->refresh($data['refresh_token']);
    } catch (\Exception $e) {
        $response = [
            'status' => false,
            'message' => 'Token refresh failed',
            'error' => $e->getMessage()
        ];
    }

    echo json_encode($response);
    exit;
}

// --------------------------------------------------
// POST /api/logout
// --------------------------------------------------
if ($_SERVER['REQUEST_URI'] === '/api/logout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = $jsonMiddleware->handle();

        if (empty($data['refresh_token'])) {
            throw new \Exception("Refresh token is required");
        }

        $response = $authController->logout($data['refresh_token']);
    } catch (\Exception $e) {
        $response = [
            'status' => false,
            'message' => 'Logout failed',
            'error' => $e->getMessage()
        ];
    }

    echo json_encode($response);
    exit;
}