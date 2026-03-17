<?php

use App\Controllers\DiscoverController;
use App\Middleware\AuthMiddleware;
use App\Middleware\JsonMiddleware;

$discoverController = new DiscoverController($db);
$authMiddleware = new AuthMiddleware($db);
$jsonMiddleware = new JsonMiddleware($db);

// GET /api/discover
if ($_SERVER['REQUEST_URI'] === '/api/discover' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $user = $authMiddleware->handle(getallheaders());
    // Fetch feed based on gender preference, distance, premium status, interactions
    $response = $discoverController->getFeed($user['id'], $user['latitude'], $user['longitude']);
    echo json_encode($response);
}
// GET /api/discover/nearby
if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/api/discover/nearby' 
    && $_SERVER['REQUEST_METHOD'] === 'GET') {
    
    $user = $authMiddleware->handle(getallheaders());

    // Parse query parameters safely
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $perPage = isset($_GET['perPage']) ? (int)$_GET['perPage'] : 20;
    $filters = [];

    if (isset($_GET['ageMin']) && isset($_GET['ageMax'])) {
        $filters['ageRange'] = [(int)$_GET['ageMin'], (int)$_GET['ageMax']];
    }

    if (isset($_GET['distance'])) {
        $filters['distance'] = (float)$_GET['distance'];
    }

    if (isset($_GET['gender'])) {
        $filters['gender'] = $_GET['gender'];
    }

    // Fetch nearby users using DiscoverController
    $response = $discoverController->getNearby($user['id'], $filters, $page, $perPage);

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}