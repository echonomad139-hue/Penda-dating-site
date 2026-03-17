<?php

use App\Controllers\PaymentController;
use App\Middleware\AuthMiddleware;
use App\Middleware\JsonMiddleware;
use App\Middleware\ValidationMiddleware;

$paymentController = new PaymentController($db);
$authMiddleware = new AuthMiddleware($db);
$jsonMiddleware = new JsonMiddleware($db);
$validationMiddleware = new ValidationMiddleware($db);

// POST /api/payment - create payment
if ($_SERVER['REQUEST_URI'] === '/api/payment' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $authMiddleware->handle(getallheaders());
    $data = $jsonMiddleware->handle();
    $data['user_id'] = $user['id'];
    $validationMiddleware->validatePayment($data);
    $response = $paymentController->createPayment($data);
    echo json_encode($response);
}

// GET /api/payment/history - fetch user payments
if ($_SERVER['REQUEST_URI'] === '/api/payment/history' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $user = $authMiddleware->handle(getallheaders());
    $response = $paymentController->getUserPayments($user['id']);
    echo json_encode($response);
}
