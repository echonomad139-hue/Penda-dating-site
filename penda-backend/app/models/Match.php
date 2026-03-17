<?php

namespace App\Models;

use PDO;

class Match extends BaseModel
{
    protected string $table = 'matches';

    /**
     * Create a match if mutual like exists
     */
    public function createMatch(int $userA, int $userB): bool
    {
        if ($userA === $userB) return false;

        // Always store smaller ID as user1_id to prevent duplicates
        [$user1, $user2] = $userA < $userB ? [$userA, $userB] : [$userB, $userA];

        // Check if match already exists
        if ($this->getMatch($user1, $user2)) return false;

        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} (user1_id, user2_id)
            VALUES (:user1, :user2)
        ");

        return $stmt->execute(['user1' => $user1, 'user2' => $user2]);
    }

    public function getMatch(int $user1, int $user2): ?array
    {
        [$u1, $u2] = $user1 < $user2 ? [$user1, $user2] : [$user2, $user1];
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE user1_id = :u1 AND user2_id = :u2
            LIMIT 1
        ");
        $stmt->execute(['u1' => $u1, 'u2' => $u2]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Delete a match (unmatch)
     */
    public function deleteMatch(int $userA, int $userB): bool
    {
        [$user1, $user2] = $userA < $userB ? [$userA, $userB] : [$userB, $userA];

        $stmt = $this->db->prepare("
            DELETE FROM {$this->table} 
            WHERE user1_id = :u1 AND user2_id = :u2
        ");
        return $stmt->execute(['u1' => $user1, 'u2' => $user2]);
    }

    /**
     * Get all matches for a user
     */
    public function getUserMatches(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE user1_id = :user OR user2_id = :user
            ORDER BY created_at DESC
        ");
        $stmt->execute(['user' => $userId]);
        return $stmt->fetchAll();
    }
}
