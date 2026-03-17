<?php

namespace App\Services;

use PDO;
use Exception;

class MatchService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /*
    |--------------------------------------------------------------------------
    | PROCESS INTERACTION
    |--------------------------------------------------------------------------
    */
    public function processInteraction(int $fromUserId, int $toUserId, string $type): array
    {
        if (!in_array($type, ['like','pass','superlike'])) {
            return ['status'=>false,'message'=>'Invalid type'];
        }

        $this->db->beginTransaction();

        try {

            // Insert interaction
            $stmt = $this->db->prepare("
                INSERT INTO interactions
                (from_user_id, to_user_id, type, created_at)
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$fromUserId, $toUserId, $type]);

            // Check mutual
            if (in_array($type, ['like','superlike'])) {

                $stmt = $this->db->prepare("
                    SELECT id FROM interactions
                    WHERE from_user_id = ?
                    AND to_user_id = ?
                    AND type IN ('like','superlike')
                ");
                $stmt->execute([$toUserId, $fromUserId]);

                if ($stmt->fetch()) {

                    // Prevent duplicate match
                    $stmt = $this->db->prepare("
                        SELECT id FROM matches
                        WHERE 
                        (user_one_id = ? AND user_two_id = ?)
                        OR
                        (user_one_id = ? AND user_two_id = ?)
                    ");
                    $stmt->execute([
                        $fromUserId, $toUserId,
                        $toUserId, $fromUserId
                    ]);

                    if (!$stmt->fetch()) {

                        $this->db->prepare("
                            INSERT INTO matches
                            (user_one_id, user_two_id, matched_at, is_active)
                            VALUES (?, ?, NOW(), 1)
                        ")->execute([$fromUserId, $toUserId]);

                        $this->db->commit();

                        return ['status'=>true,'match'=>true];
                    }
                }
            }

            $this->db->commit();
            return ['status'=>true,'match'=>false];

        } catch (Exception $e) {
            $this->db->rollBack();
            return ['status'=>false,'message'=>$e->getMessage()];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | UNMATCH
    |--------------------------------------------------------------------------
    */
    public function unmatch(int $matchId, int $userId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE matches
            SET is_active = 0
            WHERE id = ?
            AND (user_one_id = ? OR user_two_id = ?)
        ");
        return $stmt->execute([$matchId, $userId, $userId]);
    }
}
