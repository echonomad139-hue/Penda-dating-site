<?php

namespace App\Models;

use PDO;

class Message extends BaseModel
{
    protected string $table = 'messages';

    public function sendMessage(int $matchId, int $senderId, string $body): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} (match_id, sender_id, body)
            VALUES (:match_id, :sender_id, :body)
        ");

        $stmt->execute([
            'match_id' => $matchId,
            'sender_id' => $senderId,
            'body' => $body
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function markAsRead(int $messageId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE {$this->table} SET read_at = NOW() WHERE id = :id
        ");
        return $stmt->execute(['id' => $messageId]);
    }

    public function getMessagesByMatch(int $matchId, int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE match_id = :match_id 
            ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':match_id', $matchId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countUnreadMessages(int $userId): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(m.id) as unread
            FROM messages m
            JOIN matches mt ON (m.match_id = mt.id)
            WHERE m.read_at IS NULL AND (mt.user1_id = :user OR mt.user2_id = :user)
        ");
        $stmt->execute(['user' => $userId]);
        return (int) ($stmt->fetch()['unread'] ?? 0);
    }
}
