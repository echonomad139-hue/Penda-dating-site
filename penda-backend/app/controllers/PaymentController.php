<?php

namespace App\Controllers;

use PDO;
use Exception;

class PaymentController
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE PAYMENT
    |--------------------------------------------------------------------------
    */
    public function createPayment(
        int $userId,
        string $provider,
        string $transactionId,
        float $amount,
        string $currency = 'KES'
    ): array
    {
        if (!in_array($provider, ['mpesa','airtel','paypal'])) {
            return ['status' => false, 'message' => 'Invalid provider'];
        }

        // Prevent duplicate transaction
        $stmt = $this->db->prepare("
            SELECT id FROM payments WHERE transaction_id = ?
        ");
        $stmt->execute([$transactionId]);

        if ($stmt->fetch()) {
            return ['status' => false, 'message' => 'Duplicate transaction'];
        }

        $stmt = $this->db->prepare("
            INSERT INTO payments 
            (user_id, provider, transaction_id, amount, currency, status, created_at)
            VALUES (?, ?, ?, ?, ?, 'pending', NOW())
        ");

        $stmt->execute([
            $userId,
            $provider,
            $transactionId,
            $amount,
            $currency
        ]);

        return ['status' => true, 'message' => 'Payment initiated'];
    }

    /*
    |--------------------------------------------------------------------------
    | CONFIRM PAYMENT (Webhook)
    |--------------------------------------------------------------------------
    */
    public function confirmPayment(string $transactionId): array
    {
        $this->db->beginTransaction();

        try {

            // Update payment
            $stmt = $this->db->prepare("
                UPDATE payments 
                SET status = 'completed'
                WHERE transaction_id = ?
            ");
            $stmt->execute([$transactionId]);

            // Get user
            $stmt = $this->db->prepare("
                SELECT user_id FROM payments WHERE transaction_id = ?
            ");
            $stmt->execute([$transactionId]);
            $payment = $stmt->fetch();

            if (!$payment) {
                throw new Exception("Payment not found");
            }

            // Activate subscription (30 days example)
            $stmt = $this->db->prepare("
                INSERT INTO subscriptions
                (user_id, plan_type, start_date, end_date, status, created_at)
                VALUES (?, 'premium', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 'active', NOW())
            ");
            $stmt->execute([$payment['user_id']]);

            $this->db->commit();

            return ['status' => true, 'message' => 'Subscription activated'];

        } catch (Exception $e) {
            $this->db->rollBack();
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}
