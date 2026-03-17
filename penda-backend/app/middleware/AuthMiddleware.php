<?php

namespace App\Middleware;

use PDO;
use Exception;
use App\Services\JwtService;
use App\Services\RedisService;

class AuthMiddleware
{
    protected PDO $db;
    protected JwtService $jwtService;
    protected RedisService $redis;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->jwtService = new JwtService($db);
        $this->redis = new RedisService();
    }

    public function handle(array $headers): array
    {
        if (empty($headers['Authorization'])) {
            throw new Exception("Authorization header missing");
        }

        $token = str_replace('Bearer ', '', $headers['Authorization']);
        if (empty($token)) {
            throw new Exception("Token missing");
        }

        // Validate JWT
        try {
            $payload = (array) $this->jwtService->validateAccessToken($token);
        } catch (Exception $e) {
            throw new Exception("Invalid or expired token");
        }

        $userId = $payload['sub'] ?? null;
        if (!$userId) {
            throw new Exception("Token payload invalid");
        }

        // Check Redis cache
        $cacheKey = "user_profile_" . $userId;
        $cachedUser = $this->redis->get($cacheKey);
        if ($cachedUser) return $cachedUser;

        // Query DB
        $stmt = $this->db->prepare("
            SELECT 
                u.id, u.phone, u.email, u.is_verified, u.is_active, u.role,
                p.display_name, p.gender, p.date_of_birth, p.city, p.country, p.is_premium,
                s.plan, s.status AS subscription_status
            FROM users u
            LEFT JOIN profiles p ON p.user_id = u.id
            LEFT JOIN subscriptions s ON s.user_id = u.id AND s.status = 'active'
            WHERE u.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) throw new Exception("User not found");
        if (!(bool)$user['is_active']) throw new Exception("Account inactive");
        if (!(bool)$user['is_verified']) throw new Exception("Account not verified");

        // Update last_active
        $update = $this->db->prepare("UPDATE users SET last_active = NOW() WHERE id = :id");
        $update->execute(['id' => $userId]);

        // Cache result (skip if Redis unavailable)
        $this->redis->set($cacheKey, $user, 300);

        return $user;
    }
}
