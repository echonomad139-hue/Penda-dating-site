<?php

namespace App\Models;

use App\Helpers\Helpers;
use PDO;

class User extends BaseModel
{
    protected string $table = 'users';

    public function create(string $phone, string $password, ?string $email = null, string $role = 'user'): int
    {
        $hash = Helpers::hashPassword($password);

        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} (phone, email, password_hash, role) 
            VALUES (:phone, :email, :password_hash, :role)
        ");

        $stmt->execute([
            'phone' => $phone,
            'email' => $email,
            'password_hash' => $hash,
            'role' => $role
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findByPhone(string $phone): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE phone = :phone LIMIT 1");
        $stmt->execute(['phone' => $phone]);
        return $stmt->fetch() ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public function updateLastActive(int $userId): bool
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET last_active = NOW() WHERE id = :id");
        return $stmt->execute(['id' => $userId]);
    }
}
