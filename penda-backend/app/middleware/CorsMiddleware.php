<?php

namespace App\Middleware;

use PDO;
use Exception;

class CorsMiddleware
{
    protected PDO $db;
    protected array $allowedOrigins;

    public function __construct(PDO $db)
    {
        $this->db = $db;

        $origins = $_ENV['CORS_ALLOWED_ORIGINS'] ?? '*';
        $this->allowedOrigins = array_map('trim', explode(',', $origins));
    }

    public function handle(array $headers): void
    {
        $origin = $headers['Origin'] ?? '*';

        // Local environment allows all origins
        $appEnv = $_ENV['APP_ENV'] ?? 'local';
        if ($appEnv === 'local') {
            $origin = $origin ?: '*';
        } elseif (!in_array($origin, $this->allowedOrigins) && $origin !== '*') {
            throw new Exception("CORS origin not allowed: {$origin}");
        }

        header("Access-Control-Allow-Origin: {$origin}");
        header("Access-Control-Allow-Methods: " . ($_ENV['CORS_ALLOWED_METHODS'] ?? 'GET, POST, PUT, DELETE, OPTIONS'));
        header("Access-Control-Allow-Headers: " . ($_ENV['CORS_ALLOWED_HEADERS'] ?? 'Content-Type, Authorization'));
        header("Access-Control-Allow-Credentials: " . ($_ENV['CORS_ALLOW_CREDENTIALS'] ?? 'true'));
    }
}
