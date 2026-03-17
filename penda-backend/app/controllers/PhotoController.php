<?php

namespace App\Controllers;

use PDO;
use Exception;

class PhotoController
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function upload(int $userId, string $url): array
    {
        try {

            $stmt = $this->db->prepare("
                INSERT INTO photos (user_id, url, is_primary, created_at)
                VALUES (?, ?, 0, NOW())
            ");

            $stmt->execute([$userId, $url]);

            return ['status' => true, 'message' => 'Photo uploaded'];

        } catch (Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function setPrimary(int $userId, int $photoId): array
    {
        $this->db->beginTransaction();

        try {

            $this->db->prepare("
                UPDATE photos SET is_primary = 0 WHERE user_id = ?
            ")->execute([$userId]);

            $this->db->prepare("
                UPDATE photos SET is_primary = 1 WHERE id = ? AND user_id = ?
            ")->execute([$photoId, $userId]);

            $this->db->commit();

            return ['status' => true, 'message' => 'Primary photo updated'];

        } catch (Exception $e) {
            $this->db->rollBack();
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function delete(int $userId, int $photoId): array
    {
        $stmt = $this->db->prepare("
            DELETE FROM photos 
            WHERE id = ? AND user_id = ?
        ");

        $stmt->execute([$photoId, $userId]);

        return ['status' => true, 'message' => 'Photo deleted'];
    }
}
