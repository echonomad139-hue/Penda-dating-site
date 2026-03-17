<?php

namespace App\Services\Payment;

use PDO;

class PaypalService
{
    protected PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function createOrder(int $userId, float $amount): array
    {
        $tx = 'PAYPAL_' . uniqid();

        $stmt = $this->db->prepare("
            INSERT INTO payments
            (user_id, provider, transaction_id, amount, currency, status, created_at)
            VALUES
            (:user_id, 'paypal', :tx, :amount, 'USD', 'pending', NOW())
        ");

        $stmt->execute([
            'user_id' => $userId,
            'tx' => $tx,
            'amount' => $amount
        ]);

        return [
            'approval_url' => 'https://paypal.com/checkout/' . $tx,
            'transaction_id' => $tx
        ];
    }

    public function captureOrder(string $transactionId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE payments
            SET status = 'completed'
            WHERE transaction_id = :tx
        ");

        return $stmt->execute(['tx' => $transactionId]);
    }
}
