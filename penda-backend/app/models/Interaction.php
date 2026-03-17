<?php

namespace App\Models;

use PDO;
use App\Config\Database;

class Interaction extends BaseModel
{
    protected string $table = 'interactions';

    public function addInteraction(int $likerId, int $likedId, string $type): bool
    {
        if (!in_array($type, ['like', 'pass', 'superlike'])) {
            throw new \InvalidArgumentException("Invalid interaction type");
        }

        // Prevent self-interaction
        if ($likerId === $likedId) return false;

        // Check if interaction exists
        $existing = $this->getInteraction($likerId, $likedId);

        if ($existing) {
            // Update type if already exists
            $stmt = $this->db->prepare("
                UPDATE {$this->table} SET type = :type, created_at = NOW() 
                WHERE liker_id = :liker AND liked_id = :liked
            ");
            return $stmt->execute([
                'type' => $type,
                'liker' => $likerId,
                'liked' => $likedId
            ]);
        }

        // Insert new interaction
        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} (liker_id, liked_id, type)
            VALUES (:liker, :liked, :type)
        ");
        return $stmt->execute([
            'liker' => $likerId,
            'liked' => $likedId,
            'type' => $type
        ]);
    }

    public function getInteraction(int $likerId, int $likedId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE liker_id = :liker AND liked_id = :liked
            LIMIT 1
        ");
        $stmt->execute(['liker' => $likerId, 'liked' => $likedId]);
        return $stmt->fetch() ?: null;
    }

    public function bulkInteractions(int $userId, array $likedIds): array
    {
        if (empty($likedIds)) return [];

        $placeholders = implode(',', array_fill(0, count($likedIds), '?'));
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE liker_id = ? AND liked_id IN ($placeholders)
        ");

        $stmt->execute(array_merge([$userId], $likedIds));
        return $stmt->fetchAll();
    }

    public function deleteInteraction(int $likerId, int $likedId): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM {$this->table} 
            WHERE liker_id = :liker AND liked_id = :liked
        ");
        return $stmt->execute([
            'liker' => $likerId,
            'liked' => $likedId
        ]);
    }
}
