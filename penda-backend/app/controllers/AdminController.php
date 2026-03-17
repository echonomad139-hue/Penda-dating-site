<?php

namespace App\Controllers;

use PDO;
use Exception;

class AdminController
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /*
    |--------------------------------------------------------------------------
    | VERIFY ADMIN
    |--------------------------------------------------------------------------
    */
    private function ensureAdmin(int $adminId): void
    {
        $stmt = $this->db->prepare("
            SELECT role FROM users WHERE id = ? AND is_active = 1
        ");
        $stmt->execute([$adminId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || $user['role'] !== 'admin') {
            throw new Exception("Unauthorized");
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD STATS
    |--------------------------------------------------------------------------
    */
    public function dashboard(int $adminId): array
    {
        try {
            $this->ensureAdmin($adminId);

            $stats = [];

            $stats['total_users'] = $this->db->query("
                SELECT COUNT(*) FROM users
            ")->fetchColumn();

            $stats['active_users'] = $this->db->query("
                SELECT COUNT(*) FROM users WHERE is_active = 1
            ")->fetchColumn();

            $stats['verified_users'] = $this->db->query("
                SELECT COUNT(*) FROM users WHERE is_verified = 1
            ")->fetchColumn();

            $stats['total_matches'] = $this->db->query("
                SELECT COUNT(*) FROM matches WHERE is_active = 1
            ")->fetchColumn();

            $stats['active_subscriptions'] = $this->db->query("
                SELECT COUNT(*) FROM subscriptions WHERE status = 'active'
            ")->fetchColumn();

            $stats['reports_pending'] = $this->db->query("
                SELECT COUNT(*) FROM reports
            ")->fetchColumn();

            return [
                'status' => true,
                'data' => $stats
            ];

        } catch (Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GET REPORTED USERS
    |--------------------------------------------------------------------------
    */
    public function getReportedUsers(int $adminId): array
    {
        try {
            $this->ensureAdmin($adminId);

            $stmt = $this->db->query("
                SELECT 
                    r.id AS report_id,
                    r.reason,
                    r.created_at,
                    u.id AS reported_user_id,
                    p.first_name,
                    p.last_name,
                    u.phone,
                    u.email,
                    u.is_active
                FROM reports r
                JOIN users u ON u.id = r.reported_id
                JOIN profiles p ON p.user_id = u.id
                ORDER BY r.created_at DESC
            ");

            return [
                'status' => true,
                'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];

        } catch (Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DEACTIVATE USER
    |--------------------------------------------------------------------------
    */
    public function deactivateUser(int $adminId, int $userId): array
    {
        try {
            $this->ensureAdmin($adminId);

            $stmt = $this->db->prepare("
                UPDATE users SET is_active = 0 WHERE id = ?
            ");
            $stmt->execute([$userId]);

            return ['status' => true, 'message' => 'User deactivated'];

        } catch (Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | REACTIVATE USER
    |--------------------------------------------------------------------------
    */
    public function reactivateUser(int $adminId, int $userId): array
    {
        try {
            $this->ensureAdmin($adminId);

            $stmt = $this->db->prepare("
                UPDATE users SET is_active = 1 WHERE id = ?
            ");
            $stmt->execute([$userId]);

            return ['status' => true, 'message' => 'User reactivated'];

        } catch (Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE PHOTO (MODERATION)
    |--------------------------------------------------------------------------
    */
    public function deletePhoto(int $adminId, int $photoId): array
    {
        try {
            $this->ensureAdmin($adminId);

            $stmt = $this->db->prepare("
                DELETE FROM photos WHERE id = ?
            ");
            $stmt->execute([$photoId]);

            return ['status' => true, 'message' => 'Photo removed'];

        } catch (Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}
