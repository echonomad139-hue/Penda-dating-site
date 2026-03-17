<?php

namespace App\Models;

use PDO;
use App\Config\Database;

class Subscription extends BaseModel
{
    protected string $table = 'subscriptions';

    /**
     * Add a subscription
     */
    public function addSubscription(int $userId, string $plan, string $startDate, string $endDate, string $status = 'active'): int
    {
        if (!in_array($plan, ['weekly', 'monthly', 'yearly'])) {
            throw new \InvalidArgumentException("Invalid subscription plan");
        }

        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} (user_id, plan, start_date, end_date, status)
            VALUES (:user_id, :plan, :start_date, :end_date, :status)
        ");

        $stmt->execute([
            'user_id' => $userId,
            'plan' => $plan,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $status
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Get active subscription for a user
     */
    public function getActiveSubscription(int $userId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE user_id = :user_id AND status = 'active' 
            ORDER BY end_date DESC LIMIT 1
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Expire subscriptions that passed end_date
     */
    public function expireSubscriptions(): int
    {
        $stmt = $this->db->prepare("
            UPDATE {$this->table} 
            SET status = 'expired' 
            WHERE end_date < NOW() AND status = 'active'
        ");
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Cancel subscription manually
     */
    public function cancelSubscription(int $subscriptionId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE {$this->table} 
            SET status = 'cancelled' 
            WHERE id = :id
        ");
        return $stmt->execute(['id' => $subscriptionId]);
    }

    /**
     * Get all subscriptions of a user
     */
    public function getUserSubscriptions(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE user_id = :user_id
            ORDER BY start_date DESC
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }
}
