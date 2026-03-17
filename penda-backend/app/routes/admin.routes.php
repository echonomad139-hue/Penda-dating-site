<?php

use App\Controllers\AdminController;
use App\Middleware\AuthMiddleware;
use App\Middleware\AdminMiddleware;
use App\Middleware\JsonMiddleware;
use App\Middleware\ValidationMiddleware;

$adminController = new AdminController($db);
$authMiddleware = new AuthMiddleware($db);
$adminMiddleware = new AdminMiddleware($db);
$jsonMiddleware = new JsonMiddleware($db);
$validationMiddleware = new ValidationMiddleware($db);

// GET /api/admin/dashboard - admin summary
if ($_SERVER['REQUEST_URI'] === '/api/admin/dashboard' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $user = $authMiddleware->handle(getallheaders());
    $adminMiddleware->handle($user['id']);
    $response = $adminController->getDashboard();
    echo json_encode($response);
}

// GET /api/admin/profiles - list all profiles
if ($_SERVER['REQUEST_URI'] === '/api/admin/profiles' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $user = $authMiddleware->handle(getallheaders());
    $adminMiddleware->handle($user['id']);
    $response = $adminController->getAllProfiles();
    echo json_encode($response);
}

// PUT /api/admin/profile/verify - verify user photo/profile
if ($_SERVER['REQUEST_URI'] === '/api/admin/profile/verify' && $_SERVER['REQUEST_METHOD'] === 'PUT') {
    $user = $authMiddleware->handle(getallheaders());
    $adminMiddleware->handle($user['id']);
    $data = $jsonMiddleware->handle();
    $validationMiddleware->validateTable($data, 'photos', ['id', 'is_verified']);
    $response = $adminController->verifyProfile($data['id'], $data['is_verified']);
    echo json_encode($response);
}

// PUT /api/admin/report/status - update report status
if ($_SERVER['REQUEST_URI'] === '/api/admin/report/status' && $_SERVER['REQUEST_METHOD'] === 'PUT') {
    $user = $authMiddleware->handle(getallheaders());
    $adminMiddleware->handle($user['id']);
    $data = $jsonMiddleware->handle();
    $validationMiddleware->validateTable($data, 'reports', ['id', 'status']);
    $response = $adminController->updateReportStatus($data['id'], $data['status']);
    echo json_encode($response);
}
