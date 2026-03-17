<?php

namespace App\Models;

use PDO;

class Payment extends BaseModel
{
    protected string $table = 'payments';

    /**
     * Add a payment
     */
    public function addPayment(
        int $userId,
        string $provider,
        string $transactionId,
        float $amount,
        string $currency = 'KES',
        string $status = 'pending'
    ): int
    {
        if (!in_array($provider, ['mpesa', 'airtel', 'paypal'])) {
            throw new \InvalidArgumentException("Invalid payment provider");
        }

        if (!in_array($status, ['pending','completed','failed'])) {
            throw new \InvalidArgumentException("Invalid payment status");
        }

        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} (user_id, provider, transaction_id, amount, currency, status)
            VALUES (:user_id, :provider, :transaction_id, :amount, :currency, :status)
        ");

        $stmt->execute([
            'user_id' => $userId,
            'provider' => $provider,
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'currency' => $currency,
            'status' => $status
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Update payment status
     */
    public function updateStatus(string $transactionId, string $status): bool
    {
        if (!in_array($status, ['pending','completed','failed'])) {
            throw new \InvalidArgumentException("Invalid payment status");
        }

        $stmt = $this->db->prepare("
            UPDATE {$this->table} SET status = :status WHERE transaction_id = :tx_id
        ");

        return $stmt->execute([
            'status' => $status,
            'tx_id' => $transactionId
        ]);
    }

    /**
     * Get payment by transaction ID
     */
    public function getPayment(string $transactionId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} WHERE transaction_id = :tx_id LIMIT 1
        ");
        $stmt->execute(['tx_id' => $transactionId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Get all payments for a user
     */
    public function getUserPayments(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE user_id = :user_id
            ORDER BY created_at DESC
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Link a successful payment to a subscription
     */
    public function linkPaymentToSubscription(int $paymentId, int $subscriptionId): bool
    {
        // Assuming you want a join table or field in subscription to store payment_id
        $stmt = $this->db->prepare("
            UPDATE subscriptions SET payment_id = :payment_id WHERE id = :subscription_id
        ");
        return $stmt->execute([
            'payment_id' => $paymentId,
            'subscription_id' => $subscriptionId
        ]);
    }

    /**
     * Check if user has paid for active subscription
     */
    public function hasPaidForActiveSubscription(int $userId): bool
    {
        $stmt = $this->db->prepare("
            SELECT p.id FROM payments p
            JOIN subscriptions s ON s.id = p.subscription_id
            WHERE s.user_id = :user_id AND s.status = 'active' AND p.status = 'completed'
            LIMIT 1
        ");
        $stmt->execute(['user_id' => $userId]);
        return (bool) $stmt->fetchColumn();
    }
}
