<?php

namespace App\Services;

use App\Models\BaseModel;
use PDO;

class NotificationService extends BaseModel
{
    protected string $table = 'notifications';

    protected SmsService $smsService;
    protected EmailService $emailService;

    public function __construct(PDO $db)
    {
        parent::__construct($db);
        $this->smsService = new SmsService($db);
        $this->emailService = new EmailService($db);
    }

    /**
     * Create in-app notification
     */
    public function create(
        int $userId,
        string $type,
        string $title,
        string $body,
        ?int $relatedId = null
    ): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO {$this->table}
            (user_id, type, title, body, related_id, is_read, created_at)
            VALUES
            (:user_id, :type, :title, :body, :related_id, 0, NOW())
        ");

        $stmt->execute([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'related_id' => $relatedId
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE {$this->table}
            SET is_read = 1
            WHERE id = :id AND user_id = :user_id
        ");

        return $stmt->execute([
            'id' => $notificationId,
            'user_id' => $userId
        ]);
    }

    /**
     * Get user notifications
     */
    public function getUserNotifications(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM {$this->table}
            WHERE user_id = :user_id
            ORDER BY created_at DESC
        ");

        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Send multi-channel notification
     */
    public function sendMultiChannel(
        int $userId,
        string $type,
        string $title,
        string $body,
        bool $sendSms = false,
        bool $sendEmail = false
    ): void
    {
        // Save in-app
        $this->create($userId, $type, $title, $body);

        // Fetch user info
        $stmt = $this->db->prepare("
            SELECT email, phone, language
            FROM users
            WHERE id = :id
        ");
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();

        if (!$user) return;

        if ($sendSms && !empty($user['phone'])) {
            $this->smsService->send($user['phone'], $body);
        }

        if ($sendEmail && !empty($user['email'])) {
            $this->emailService->send(
                $user['email'],
                $title,
                $body,
                $user['language'] ?? 'en'
            );
        }
    }

    /**
     * Notify when new match happens
     */
    public function notifyNewMatch(int $userId, int $matchId): void
    {
        $this->create(
            $userId,
            'match',
            '💞 It’s a Match!',
            'You have a new match. Start chatting now.',
            $matchId
        );
    }

    /**
     * Notify when new message arrives
     */
    public function notifyNewMessage(int $receiverId, int $messageId): void
    {
        $this->create(
            $receiverId,
            'message',
            '💬 New Message',
            'You received a new message.',
            $messageId
        );
    }
}
