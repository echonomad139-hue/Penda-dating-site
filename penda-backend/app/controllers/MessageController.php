<?php

namespace App\Controllers;

use PDO;
use Exception;

class MessageController
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /*
    |--------------------------------------------------------------------------
    | SEND MESSAGE
    |--------------------------------------------------------------------------
    */
    public function sendMessage(int $matchId, int $senderId, string $messageText): array
    {
        try {

            // Validate active match + ownership
            $stmt = $this->db->prepare("
                SELECT * FROM matches
                WHERE id = ?
                AND is_active = 1
                AND (user_one_id = ? OR user_two_id = ?)
            ");
            $stmt->execute([$matchId, $senderId, $senderId]);
            $match = $stmt->fetch();

            if (!$match) {
                return ['status' => false, 'message' => 'Invalid match'];
            }

            // Check block
            $stmt = $this->db->prepare("
                SELECT id FROM blocks
                WHERE (blocker_id = ? AND blocked_id = ?)
                OR (blocker_id = ? AND blocked_id = ?)
            ");
            $stmt->execute([
                $match['user_one_id'],
                $match['user_two_id'],
                $match['user_two_id'],
                $match['user_one_id']
            ]);

            if ($stmt->fetch()) {
                return ['status' => false, 'message' => 'User blocked'];
            }

            // Insert message
            $stmt = $this->db->prepare("
                INSERT INTO messages (match_id, sender_id, message_text, is_read, created_at)
                VALUES (?, ?, ?, 0, NOW())
            ");
            $stmt->execute([$matchId, $senderId, $messageText]);

            return [
                'status' => true,
                'message' => 'Message sent'
            ];

        } catch (Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GET CONVERSATION
    |--------------------------------------------------------------------------
    */
    public function getMessages(int $matchId, int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT m.*
            FROM messages m
            JOIN matches ma ON ma.id = m.match_id
            WHERE m.match_id = ?
            AND (ma.user_one_id = ? OR ma.user_two_id = ?)
            AND ma.is_active = 1
            ORDER BY m.created_at ASC
        ");

        $stmt->execute([$matchId, $userId, $userId]);

        return [
            'status' => true,
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | MARK AS READ
    |--------------------------------------------------------------------------
    */
    public function markAsRead(int $matchId, int $userId): array
    {
        $stmt = $this->db->prepare("
            UPDATE messages m
            JOIN matches ma ON ma.id = m.match_id
            SET m.is_read = 1
            WHERE m.match_id = ?
            AND ma.is_active = 1
            AND m.sender_id != ?
        ");

        $stmt->execute([$matchId, $userId]);

        return ['status' => true, 'message' => 'Messages marked as read'];
    }
}
