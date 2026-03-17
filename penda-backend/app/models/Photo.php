<?php

namespace App\Models;

use PDO;

class Photo extends BaseModel
{
    protected string $table = 'photos';

    public function addPhoto(int $userId, string $url, bool $isPrimary = false): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} (user_id, url, is_primary) 
            VALUES (:user_id, :url, :is_primary)
        ");

        $stmt->execute([
            'user_id' => $userId,
            'url' => $url,
            'is_primary' => $isPrimary ? 1 : 0
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function getUserPhotos(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = :user_id ORDER BY order_index ASC");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function setPrimary(int $photoId, int $userId): bool
    {
        // Reset all photos
        $stmt = $this->db->prepare("UPDATE {$this->table} SET is_primary = 0 WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);

        // Set selected photo as primary
        $stmt2 = $this->db->prepare("UPDATE {$this->table} SET is_primary = 1 WHERE id = :id AND user_id = :user_id");
        return $stmt2->execute(['id' => $photoId, 'user_id' => $userId]);
    }

    public function deletePhoto(int $photoId, int $userId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id AND user_id = :user_id");
        return $stmt->execute(['id' => $photoId, 'user_id' => $userId]);
    }
}
