<?php

namespace App\Models;

use PDO;

class Report extends BaseModel
{
    protected string $table = 'reports';

    /**
     * Add a report
     */
    public function addReport(int $reporterId, int $reportedId, string $reason, ?string $description = null): int
    {
        if ($reporterId === $reportedId) throw new \Exception("Cannot report yourself");

        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} (reporter_id, reported_id, reason, description)
            VALUES (:reporter_id, :reported_id, :reason, :description)
        ");

        $stmt->execute([
            'reporter_id' => $reporterId,
            'reported_id' => $reportedId,
            'reason' => $reason,
            'description' => $description
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Get reports by status
     */
    public function getReportsByStatus(string $status = 'pending'): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE status = :status
            ORDER BY created_at DESC
        ");
        $stmt->execute(['status' => $status]);
        return $stmt->fetchAll();
    }

    /**
     * Update report status
     */
    public function updateStatus(int $reportId, string $status): bool
    {
        if (!in_array($status, ['pending','reviewed','resolved','rejected'])) {
            throw new \InvalidArgumentException("Invalid report status");
        }

        $stmt = $this->db->prepare("
            UPDATE {$this->table} 
            SET status = :status 
            WHERE id = :id
        ");
        return $stmt->execute(['status' => $status, 'id' => $reportId]);
    }

    /**
     * Count reports per user
     */
    public function countReportsForUser(int $userId): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total FROM {$this->table} 
            WHERE reported_id = :user_id
        ");
        $stmt->execute(['user_id' => $userId]);
        return (int) ($stmt->fetch()['total'] ?? 0);
    }
}
