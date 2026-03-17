<?php

namespace App\Controllers;

use PDO;

class ReportController
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
            return ['status' => false, 'message' => 'Cannot report yourself'];
        }

        // Prevent duplicate report
        $stmt = $this->db->prepare("
            SELECT id FROM reports
            WHERE reporter_id = ? AND reported_id = ?
        ");
        $stmt->execute([$reporterId, $reportedId]);

        if ($stmt->fetch()) {
            return ['status' => false, 'message' => 'Already reported'];
        }

        $stmt = $this->db->prepare("
            INSERT INTO reports (reporter_id, reported_id, reason, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$reporterId, $reportedId, $reason]);

        return ['status' => true, 'message' => 'User reported'];
    }

    /*
    |--------------------------------------------------------------------------
    | BLOCK USER
    |--------------------------------------------------------------------------
    */
    public function blockUser(int $blockerId, int $blockedId): array
    {
        if ($blockerId === $blockedId) {
            return ['status' => false, 'message' => 'Cannot block yourself'];
        }

        $stmt = $this->db->prepare("
            SELECT id FROM blocks
            WHERE blocker_id = ? AND blocked_id = ?
        ");
        $stmt->execute([$blockerId, $blockedId]);

        if ($stmt->fetch()) {
            return ['status' => false, 'message' => 'Already blocked'];
        }

        $stmt = $this->db->prepare("
            INSERT INTO blocks (blocker_id, blocked_id, created_at)
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$blockerId, $blockedId]);

        return ['status' => true, 'message' => 'User blocked'];
    }
}
