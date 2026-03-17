<?php

namespace App\Models;

use PDO;

class Profile extends BaseModel
{
    protected string $table = 'profiles';

    public function create(int $userId, string $displayName, string $dob, string $gender, ?string $bio = null, ?string $relationshipIntent = null): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} 
            (user_id, display_name, date_of_birth, gender, bio, relationship_intent) 
            VALUES 
            (:user_id, :display_name, :dob, :gender, :bio, :relationship_intent)
        ");

        return $stmt->execute([
            'user_id' => $userId,
            'display_name' => $displayName,
            'dob' => $dob,
            'gender' => $gender,
            'bio' => $bio,
            'relationship_intent' => $relationshipIntent
        ]);
    }

    public function findByUserId(int $userId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = :user_id LIMIT 1");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch() ?: null;
    }

    public function updateBio(int $userId, string $bio): bool
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET bio = :bio, updated_at = NOW() WHERE user_id = :user_id");
        return $stmt->execute(['bio' => $bio, 'user_id' => $userId]);
    }
}
