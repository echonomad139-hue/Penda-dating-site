<?php

namespace App\Middleware;

use PDO;
use Exception;
use App\Middleware\AuthMiddleware;
use App\Services\JwtService;

class RouteGuard
{
    protected PDO $db;
    protected AuthMiddleware $auth;
    protected JwtService $jwtService;

    /**
     * Routes that require authentication
     */
    protected array $protectedRoutes = [
        '/user/profile' => ['GET', 'PUT'],
        '/user/settings' => ['GET', 'PUT'],
        '/user/logout' => ['POST'],
        '/payments/process' => ['POST'],
        '/messages/send' => ['POST'],
    ];

    public function __construct(PDO $db)
    {
        $this->db = $db;

        // Use AuthMiddleware (handles optional Redis internally)
        $this->auth = new AuthMiddleware($db);

        // JWT service for additional validation if needed
        $this->jwtService = new JwtService($db);
    }

    /**
     * Protect a route
     *
     * @param string $uri
     * @param string $method
     * @param array $headers
     * @return array Authenticated user info
     * @throws Exception
     */
    public function protect(string $uri, string $method, array $headers): array
    {
        $uri = rtrim($uri, '/');

        foreach ($this->protectedRoutes as $route => $methods) {
            if ($route === $uri && in_array($method, $methods)) {
                // Delegate authentication to AuthMiddleware
                return $this->auth->handle($headers);
            }
        }

        // Route not protected → return empty
        return [];
    }

    /**
     * Dynamically add a protected route
     */
    public function addProtectedRoute(string $route, array $methods = ['GET', 'POST']): void
    {
        $this->protectedRoutes[rtrim($route, '/')] = $methods;
    }
}
