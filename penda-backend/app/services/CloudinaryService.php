<?php

namespace App\Services;

use PDO;
use Exception;

class CloudinaryService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /*
    |--------------------------------------------------------------------------
    | SAVE UPLOADED PHOTO
    |--------------------------------------------------------------------------
    */
    public function savePhoto(int $userId, string $cloudinaryUrl, bool $isPrimary = false): array
    {
        try {

            $this->db->beginTransaction();

            if ($isPrimary) {
                $this->db->prepare("
                    UPDATE photos SET is_primary = 0
                    WHERE user_id = ?
                ")->execute([$userId]);
            }

            $stmt = $this->db->prepare("
                INSERT INTO photos
                (user_id, url, is_primary, created_at)
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([
                $userId,
                $cloudinaryUrl,
                $isPrimary ? 1 : 0
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
    | DELETE PHOTO
    |--------------------------------------------------------------------------
    */
    public function deletePhoto(int $userId, int $photoId): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM photos
            WHERE id = ?
            AND user_id = ?
        ");

        return $stmt->execute([$photoId, $userId]);
    }

    /*
    |--------------------------------------------------------------------------
    | GET USER PHOTOS
    |--------------------------------------------------------------------------
    */
    public function getUserPhotos(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT id, url, is_primary, created_at
            FROM photos
            WHERE user_id = ?
            ORDER BY is_primary DESC, created_at DESC
        ");
        $stmt->execute([$userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
