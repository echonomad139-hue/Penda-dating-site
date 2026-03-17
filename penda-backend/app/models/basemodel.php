<?php

namespace App\Models;

use App\Config\Database;
use PDO;

abstract class BaseModel
{
    protected PDO $db;
    protected string $table;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Get all rows
     */
    public function all(): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll();
    }

    /**
     * Find by ID
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Delete by ID
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
