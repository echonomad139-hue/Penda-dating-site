<?php

namespace App\Controllers;

use PDO;
use Exception;

class SettingsController
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /*
    |--------------------------------------------------------------------------
    | GET SETTINGS
    |--------------------------------------------------------------------------
    */
    public function getSettings(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM user_settings WHERE user_id = ?
        ");
        $stmt->execute([$userId]);

        $settings = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$settings) {
            return ['status' => false, 'message' => 'Settings not found'];
        }

        return ['status' => true, 'data' => $settings];
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE SETTINGS
    |--------------------------------------------------------------------------
    */
    public function updateSettings(int $userId, array $data): array
    {
        try {

            $stmt = $this->db->prepare("
                UPDATE user_settings SET
                    language = ?,
                    notifications_enabled = ?,
                    show_online_status = ?,
                    updated_at = NOW()
                WHERE user_id = ?
            ");

            $stmt->execute([
                $data['language'],
                $data['notifications_enabled'],
                $data['show_online_status'],
                $userId
            ]);

            return ['status' => true, 'message' => 'Settings updated'];

        } catch (Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CHANGE EMAIL
    |--------------------------------------------------------------------------
    */
    public function changeEmail(int $userId, string $newEmail): array
    {
        $stmt = $this->db->prepare("
            UPDATE users SET email = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$newEmail, $userId]);

        return ['status' => true, 'message' => 'Email updated'];
    }

    /*
    |--------------------------------------------------------------------------
    | CHANGE PASSWORD
    |--------------------------------------------------------------------------
    */
    public function changePassword(int $userId, string $newPassword): array
    {
        $hashed = password_hash($newPassword, PASSWORD_BCRYPT);

        $stmt = $this->db->prepare("
            UPDATE users SET password_hash = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$hashed, $userId]);

        return ['status' => true, 'message' => 'Password changed'];
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE ACCOUNT (SOFT DELETE)
    |--------------------------------------------------------------------------
    */
    public function deleteAccount(int $userId): array
    {
        $stmt = $this->db->prepare("
            UPDATE users SET is_active = 0, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$userId]);

        return ['status' => true, 'message' => 'Account deactivated'];
    }
}
