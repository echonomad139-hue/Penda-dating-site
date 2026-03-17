<?php
declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';
use App\Middleware\CorsMiddleware;
use App\Middleware\JsonMiddleware;
use App\Middleware\LogMiddleware;
use App\Middleware\RateLimitMiddleware;
use App\Middleware\ValidationMiddleware;

use App\Controllers\AuthController;
use App\Controllers\ProfileController;
use App\Controllers\DiscoverController;
use App\Controllers\MatchController;
use App\Controllers\MessageController;
use App\Controllers\ReportController;
use App\Controllers\AdminController;
use App\Controllers\PasswordController;

define('BASE_PATH', dirname(__DIR__));

/*
|--------------------------------------------------------------------------
| DEVELOPMENT ERROR DISPLAY
|--------------------------------------------------------------------------
*/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('log_errors', 1);
ini_set('error_log', BASE_PATH . '/storage/logs/error.log');

/*
|--------------------------------------------------------------------------
| BASIC CORS (required for Vite localhost:5173)
|--------------------------------------------------------------------------
*/
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

/* Handle preflight request */
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

/*
|--------------------------------------------------------------------------
| AUTOLOAD
|--------------------------------------------------------------------------
*/
require BASE_PATH . '/vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| LOAD ENV
|--------------------------------------------------------------------------
*/
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->safeLoad();

$appEnv = $_ENV['APP_ENV'] ?? 'local';
$isProduction = $appEnv === 'production';

/*
|--------------------------------------------------------------------------
| SECURITY HEADERS
|--------------------------------------------------------------------------
*/
header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: no-referrer');

if ($isProduction) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

/*
|--------------------------------------------------------------------------
| GET URI
|--------------------------------------------------------------------------
*/
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$basePath = '/penda-backend/public';

if (strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}

$uri = '/' . trim($uri, '/');

if ($uri === '//') {
    $uri = '/';
}

/*
|--------------------------------------------------------------------------
| DATABASE CONNECTION
|--------------------------------------------------------------------------
*/
$pdo = new PDO(
    "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_DATABASE']};charset=utf8mb4",
    $_ENV['DB_USERNAME'],
    $_ENV['DB_PASSWORD'],
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]
);

/*
|--------------------------------------------------------------------------
| MIDDLEWARE
|--------------------------------------------------------------------------
*/
$rateLimiter = new RateLimitMiddleware(
    $pdo,
    (int)($_ENV['API_RATE_LIMIT'] ?? 1000),
    (int)($_ENV['API_RATE_LIMIT_WINDOW'] ?? 60)
);
$rateLimiter->handle();

$allowedOrigins = $isProduction
    ? ['https://your-production-frontend.com']
    : ['*'];

$cors = new CorsMiddleware($pdo, $allowedOrigins);
$json = new JsonMiddleware($pdo);
$log  = new LogMiddleware($pdo, BASE_PATH . '/storage/logs/');
$validation = new ValidationMiddleware($pdo);

$cors->handle(['Origin' => $_SERVER['HTTP_ORIGIN'] ?? '']);
$requestData = $json->handle();

/*
|--------------------------------------------------------------------------
| CONTROLLERS
|--------------------------------------------------------------------------
*/
$authController = new AuthController($pdo);
$profileController = new ProfileController($pdo);
$discoverController = new DiscoverController($pdo);
$matchController = new MatchController($pdo);
$messageController = new MessageController($pdo);
$reportController = new ReportController($pdo);
$adminController = new AdminController($pdo);
$passwordController = new PasswordController($pdo);

/*
|--------------------------------------------------------------------------
| ROUTER
|--------------------------------------------------------------------------
*/
try {

    $method = $_SERVER['REQUEST_METHOD'];

    /*
    |--------------------------------------------------------------------------
    | ROOT ROUTE
    |--------------------------------------------------------------------------
    */
    if ($uri === '/' && $method === 'GET') {

        echo json_encode([
            'success' => true,
            'message' => 'Penda API is running'
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | AUTH ROUTES
    |--------------------------------------------------------------------------
    */

    if ($uri === '/api/register' && $method === 'POST') {

        $validation->validateRegister($requestData);

        $response = $authController->register($requestData);

        echo json_encode($response);
        exit;
    }

    if ($uri === '/api/login' && $method === 'POST') {

        $response = $authController->login($requestData);

        echo json_encode($response);
        exit;
    }

    if ($uri === '/api/register/request-otp' && $method === 'POST') {

    $response = $authController->requestRegistrationOTP($requestData);

    echo json_encode($response);
    exit;
}

if ($uri === '/api/register/verify-otp' && $method === 'POST') {

    $response = $authController->verifyRegistrationOTP($requestData);

    echo json_encode($response);
    exit;
}

    if ($uri === '/api/refresh' && $method === 'POST') {

        $response = $authController->refresh($requestData['refresh_token'] ?? '');

        echo json_encode($response);
        exit;
    }

    if ($uri === '/api/logout' && $method === 'POST') {

        $response = $authController->logout($requestData['refresh_token'] ?? '');

        echo json_encode($response);
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | PROFILE ROUTES
    |--------------------------------------------------------------------------
    */

    if ($uri === '/api/profile' && $method === 'GET') {

        error_log("HEADERS: " . json_encode(getallheaders()));
        $user = $authController->jwtService->getUserFromHeaders(getallheaders());

        $response = $profileController->getProfile($user['id']);

        echo json_encode($response);
        exit;
    }

    if ($uri === '/api/profile' && $method === 'PUT') {

    error_log("HEADERS: " . json_encode(getallheaders()));
    $user = $authController->jwtService->getUserFromHeaders(getallheaders());

    $requestData['user_id'] = $user['id'];

    $response = $profileController->setupProfile($requestData);

    echo json_encode($response);
    exit;
   }

    /*
    |--------------------------------------------------------------------------
    | DISCOVER ROUTE
    |--------------------------------------------------------------------------
    */

    if ($uri === '/api/discover' && $method === 'GET') {

        error_log("HEADERS: " . json_encode(getallheaders()));
        $user = $authController->jwtService->getUserFromHeaders(getallheaders());

        $response = $discoverController->getFeed($user['id']);

        echo json_encode($response);
        exit;
    }

     /*
    |--------------------------------------------------------------------------
    | request otp
    |--------------------------------------------------------------------------
    */
    if ($uri === '/api/password/request-otp' && $method === 'POST') {

    $response = $passwordController->requestOTP($requestData);

    echo json_encode($response);
    exit;
    }
      /*
    |--------------------------------------------------------------------------
    | verify otp
    |--------------------------------------------------------------------------
    */
    if ($uri === '/api/password/verify-otp' && $method === 'POST') {

    $response = $passwordController->verifyOTP($requestData);

    echo json_encode($response);
    exit;
    }
      /*
    |--------------------------------------------------------------------------
    | Reset password
    |--------------------------------------------------------------------------
    */
    if ($uri === '/api/password/reset' && $method === 'POST') {

    $response = $passwordController->resetPassword($requestData);

    echo json_encode($response);
    exit;
    }
    /*
    |--------------------------------------------------------------------------
    | ROUTE NOT FOUND
    |--------------------------------------------------------------------------
    */

    http_response_code(404);

    echo json_encode([
        'success' => false,
        'message' => "Route {$uri} not found"
    ]);

} catch (\Throwable $e) {

    error_log($e->__toString());

    if (isset($log)) {
        $log->logError($e);
    }

    echo json_encode([
        'success' => false,
        'message' => 'Server error',
        'error' => $e->getMessage()
    ]);
}

 