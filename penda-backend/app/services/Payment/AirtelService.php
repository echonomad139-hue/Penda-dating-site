<?php

namespace App\Services\Payment;

use PDO;

class AirtelService
{
    protected PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function initiatePayment(int $userId, float $amount): array
    {
        $tx = 'AIRTEL_' . uniqid();

        $stmt = $this->db->prepare("
            INSERT INTO payments
            (user_id, provider, transaction_id, amount, currency, status, created_at)
            VALUES
            (:user_id, 'airtel', :tx, :amount, 'KES', 'pending', NOW())
        ");

        $stmt->execute([
            'user_id' => $userId,
            'tx' => $tx,
            'amount' => $amount
        ]);

        return [
            'transaction_id' => $tx,
            'status' => 'initiated'
        ];
    }

    public function confirmPayment(string $transactionId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE payments 
            SET status = 'completed'
            WHERE transaction_id = :tx
        ");

        return $stmt->execute(['tx' => $transactionId]);
    }
}
