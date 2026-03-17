<?php

namespace App\Middleware;

use PDO;

class AdminMiddleware
{
    protected PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function handle(int $userId): bool
    {
        $stmt = $this->db->prepare("
            SELECT role, is_active 
            FROM users 
            WHERE id = :id
        ");
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();

        if (!$user || $user['role'] !== 'admin') {
            throw new \Exception("Access denied. Admins only");
        }

        if (!$user['is_active']) {
            throw new \Exception("Admin account inactive");
        }

        return true;
    }
}
