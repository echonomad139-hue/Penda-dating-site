<?php

namespace App\Services;

use PDO;
use Exception;

class ModerationService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /*
    |--------------------------------------------------------------------------
    | REPORT USER
    |--------------------------------------------------------------------------
    */
    public function reportUser(int $reporterId, int $reportedId, string $reason): array
    {
        if ($reporterId === $reportedId) {
            return ['status'=>false,'message'=>'Cannot report yourself'];
        }

        $stmt = $this->db->prepare("
            INSERT INTO reports
            (reporter_id, reported_id, reason, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$reporterId, $reportedId, $reason]);

        return ['status'=>true];
    }

    /*
    |--------------------------------------------------------------------------
    | BLOCK USER
    |--------------------------------------------------------------------------
    */
    public function blockUser(int $blockerId, int $blockedId): array
    {
        $this->db->beginTransaction();

        try {

            // Insert block
            $stmt = $this->db->prepare("
                INSERT INTO blocks
                (blocker_id, blocked_id, created_at)
                VALUES (?, ?, NOW())
            ");
            $stmt->execute([$blockerId, $blockedId]);

            // Deactivate existing match
            $stmt = $this->db->prepare("
                UPDATE matches
                SET is_active = 0
                WHERE 
                (user_one_id = ? AND user_two_id = ?)
                OR
                (user_one_id = ? AND user_two_id = ?)
            ");
            $stmt->execute([
                $blockerId, $blockedId,
                $blockedId, $blockerId
            ]);

            $this->db->commit();
            return ['status'=>true];

        } catch (Exception $e) {
            $this->db->rollBack();
            return ['status'=>false,'message'=>$e->getMessage()];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | AUTO SUSPEND IF MANY REPORTS
    |--------------------------------------------------------------------------
    */
    public function autoSuspendIfThresholdReached(int $userId, int $threshold = 5): void
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM reports
            WHERE reported_id = ?
        ");
        $stmt->execute([$userId]);
        $count = $stmt->fetchColumn();

        if ($count >= $threshold) {
            $this->db->prepare("
                UPDATE users
                SET is_active = 0
                WHERE id = ?
            ")->execute([$userId]);
        }
    }
}
