<?php

namespace App\Controllers;

use PDO;

class MatchController
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getMatches(int $userId): array
    {
        $sql = "
            SELECT 
                m.id AS match_id,
                u.id AS user_id,
                p.first_name,
                p.last_name,
                ph.url AS primary_photo,
                m.matched_at
            FROM matches m
            JOIN users u 
                ON u.id = 
                    CASE 
                        WHEN m.user_one_id = :user_id 
                        THEN m.user_two_id 
                        ELSE m.user_one_id 
                    END
            JOIN profiles p ON p.user_id = u.id
            LEFT JOIN photos ph 
                ON ph.user_id = u.id AND ph.is_primary = 1
            WHERE (m.user_one_id = :user_id OR m.user_two_id = :user_id)
            AND m.is_active = 1
            ORDER BY m.matched_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        return [
            'status' => true,
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
        ];
    }

    public function unmatch(int $userId, int $matchId): array
    {
        $stmt = $this->db->prepare("
            UPDATE matches
            SET is_active = 0
            WHERE id = ?
            AND (user_one_id = ? OR user_two_id = ?)
        ");

        $stmt->execute([$matchId, $userId, $userId]);

        return [
            'status' => true,
            'message' => 'Unmatched successfully'
        ];
    }
}
