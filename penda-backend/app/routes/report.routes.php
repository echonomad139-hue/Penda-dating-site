<?php

use App\Controllers\ReportController;
use App\Middleware\AuthMiddleware;
use App\Middleware\JsonMiddleware;
use App\Middleware\ValidationMiddleware;

$reportController = new ReportController($db);
$authMiddleware = new AuthMiddleware($db);
$jsonMiddleware = new JsonMiddleware($db);
$validationMiddleware = new ValidationMiddleware($db);

// POST /api/report - report a user
if ($_SERVER['REQUEST_URI'] === '/api/report' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $authMiddleware->handle(getallheaders());
    $data = $jsonMiddleware->handle();
    $data['reporter_id'] = $user['id'];
    $validationMiddleware->validateReport($data);
    $response = $reportController->reportUser($data);
    echo json_encode($response);
}

// POST /api/block - block a user
if ($_SERVER['REQUEST_URI'] === '/api/block' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $authMiddleware->handle(getallheaders());
    $data = $jsonMiddleware->handle();
    $data['blocker_id'] = $user['id'];
    $validationMiddleware->validateTable($data, 'blocks', ['blocker_id', 'blocked_id']);
    $response = $reportController->blockUser($data);
    echo json_encode($response);
}

// GET /api/reports - fetch user reports
if ($_SERVER['REQUEST_URI'] === '/api/reports' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $user = $authMiddleware->handle(getallheaders());
    $response = $reportController->getUserReports($user['id']);
    echo json_encode($response);
}
