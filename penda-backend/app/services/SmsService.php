<?php

namespace App\Services;

use PDO;

class SmsService
{
    protected PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Send SMS
     */
    public function send(string $phone, string $message): bool
    {
        // Here you would integrate:
        // Mpesa API / Airtel SMS API / Twilio etc

        // Log to DB
        $stmt = $this->db->prepare("
            INSERT INTO sms_logs (phone, message, status, created_at)
            VALUES (:phone, :message, 'sent', NOW())
        ");

        return $stmt->execute([
            'phone' => $phone,
            'message' => $message
        ]);
    }

    /**
     * Send OTP SMS
     */
    public function sendOtp(string $phone, string $otp): bool
    {
        return $this->send($phone, "Your Penda OTP is: {$otp}");
    }

    /**
     * Send payment confirmation
     */
    public function sendPaymentReceipt(string $phone, float $amount): bool
    {
        return $this->send($phone, "Payment of {$amount} received. Thank you for subscribing to Penda Premium 👑");
    }
}
