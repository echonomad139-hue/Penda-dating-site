<?php

namespace App\Services\Payment;

use PDO;
use App\Services\NotificationService;

class MpesaService
{
    protected PDO $db;
    protected NotificationService $notification;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->notification = new NotificationService($db);
    }

    /**
     * Initiate STK Push
     */
    public function initiatePayment(int $userId, float $amount): array
    {
        $transactionId = 'MPESA_' . uniqid();

        $stmt = $this->db->prepare("
            INSERT INTO payments 
            (user_id, provider, transaction_id, amount, currency, status, created_at)
            VALUES 
            (:user_id, 'mpesa', :tx, :amount, 'KES', 'pending', NOW())
        ");

        $stmt->execute([
            'user_id' => $userId,
            'tx' => $transactionId,
            'amount' => $amount
        ]);

        // Here you integrate real Mpesa API

        return [
            'transaction_id' => $transactionId,
            'status' => 'initiated'
        ];
    }

    /**
     * Handle Mpesa callback
     */
    public function handleCallback(string $transactionId, bool $success): bool
    {
        $status = $success ? 'completed' : 'failed';

        $stmt = $this->db->prepare("
            UPDATE payments 
            SET status = :status 
            WHERE transaction_id = :tx
        ");
        $stmt->execute([
            'status' => $status,
            'tx' => $transactionId
        ]);

        if ($success) {
            $this->activateSubscription($transactionId);
        }

        return true;
    }

    /**
     * Activate subscription after successful payment
     */
    private function activateSubscription(string $transactionId): void
    {
        $payment = $this->db->prepare("
            SELECT id, user_id, amount 
            FROM payments 
            WHERE transaction_id = :tx
        ");
        $payment->execute(['tx' => $transactionId]);
        $data = $payment->fetch();

        if (!$data) return;

        $expires = date('Y-m-d H:i:s', strtotime('+30 days'));

        $stmt = $this->db->prepare("
            INSERT INTO subscriptions
            (user_id, plan_type, status, starts_at, expires_at, payment_id)
            VALUES
            (:user_id, 'premium', 'active', NOW(), :expires, :payment_id)
        ");

        $stmt->execute([
            'user_id' => $data['user_id'],
            'expires' => $expires,
            'payment_id' => $data['id']
        ]);

        $this->notification->sendMultiChannel(
            $data['user_id'],
            'payment',
            'Premium Activated 👑',
            'Your premium subscription is now active!',
            true,
            true
        );
    }
}
