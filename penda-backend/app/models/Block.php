<?php

namespace App\Models;

use PDO;

class Block extends BaseModel
{
    protected string $table = 'blocks';

    /**
     * Block a user
     */
    public function blockUser(int $blockerId, int $blockedId): bool
    {
        if ($blockerId === $blockedId) return false;

        // Check if already blocked
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE blocker_id = :blocker AND blocked_id = :blocked
            LIMIT 1
        ");
        $stmt->execute(['blocker' => $blockerId, 'blocked' => $blockedId]);
        if ($stmt->fetch()) return false;

        $stmt2 = $this->db->prepare("
            INSERT INTO {$this->table} (blocker_id, blocked_id) 
            VALUES (:blocker, :blocked)
        ");
        return $stmt2->execute(['blocker' => $blockerId, 'blocked' => $blockedId]);
    }

    /**
     * Unblock a user
     */
    public function unblockUser(int $blockerId, int $blockedId): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM {$this->table} 
            WHERE blocker_id = :blocker AND blocked_id = :blocked
        ");
        return $stmt->execute(['blocker' => $blockerId, 'blocked' => $blockedId]);
    }

    /**
     * Check if a user is blocked
     */
    public function isBlocked(int $blockerId, int $blockedId): bool
    {
        $stmt = $this->db->prepare("
            SELECT 1 FROM {$this->table} 
            WHERE blocker_id = :blocker AND blocked_id = :blocked
            LIMIT 1
        ");
        $stmt->execute(['blocker' => $blockerId, 'blocked' => $blockedId]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Get all users blocked by a user
     */
    public function getBlockedUsers(int $blockerId): array
    {
        $stmt = $this->db->prepare("
            SELECT blocked_id FROM {$this->table} 
            WHERE blocker_id = :blocker
        ");
        $stmt->execute(['blocker' => $blockerId]);
        return array_column($stmt->fetchAll(), 'blocked_id');
    }

    /**
     * Check if two users have mutually blocked each other
     */
    public function isMutuallyBlocked(int $userA, int $userB): bool
    {
        return $this->isBlocked($userA, $userB) && $this->isBlocked($userB, $userA);
    }
}
