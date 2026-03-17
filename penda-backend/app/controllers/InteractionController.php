<?php

namespace App\Controllers;

use PDO;
use Exception;

class InteractionController
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function interact(int $fromUserId, int $toUserId, string $type): array
    {
        if ($fromUserId === $toUserId) {
            return ['status' => false, 'message' => 'Cannot interact with yourself'];
        }

        if (!in_array($type, ['like', 'pass', 'superlike'])) {
            return ['status' => false, 'message' => 'Invalid interaction type'];
        }

        try {
            $this->db->beginTransaction();

            // Prevent duplicate interaction
            $stmt = $this->db->prepare("
                SELECT id FROM interactions
                WHERE from_user_id = ? AND to_user_id = ?
            ");
            $stmt->execute([$fromUserId, $toUserId]);

            if ($stmt->fetch()) {
                return ['status' => false, 'message' => 'Already interacted'];
            }

            // Insert interaction
            $stmt = $this->db->prepare("
                INSERT INTO interactions (from_user_id, to_user_id, type, created_at)
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$fromUserId, $toUserId, $type]);

            // If LIKE or SUPERLIKE, check mutual
            if (in_array($type, ['like','superlike'])) {

                $stmt = $this->db->prepare("
                    SELECT id FROM interactions
                    WHERE from_user_id = ? 
                    AND to_user_id = ?
                    AND type IN ('like','superlike')
                ");
                $stmt->execute([$toUserId, $fromUserId]);

                if ($stmt->fetch()) {

                    // Create match
                    $stmt = $this->db->prepare("
                        INSERT INTO matches 
                        (user_one_id, user_two_id, matched_at, is_active)
                        VALUES (?, ?, NOW(), 1)
                    ");
                    $stmt->execute([$fromUserId, $toUserId]);

                    $this->db->commit();

                    return [
                        'status' => true,
                        'match' => true,
                        'message' => 'It’s a match!'
                    ];
                }
            }

            $this->db->commit();

            return [
                'status' => true,
                'match' => false,
                'message' => 'Interaction saved'
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}
