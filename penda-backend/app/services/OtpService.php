<?php

namespace App\Services;

use PDO;

class OtpService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /*
    |--------------------------------------------
    | GENERATE OTP
    |--------------------------------------------
    */
    public function generateOTP(int $userId): string
    {
        $otp = (string) random_int(100000, 999999);

        $stmt = $this->db->prepare("
            INSERT INTO otp_verifications
            (user_id, otp_code, expires_at, created_at)
            VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE), NOW())
        ");

        $stmt->execute([$userId, $otp]);

        return $otp;
    }

    /*
    |--------------------------------------------
    | VERIFY OTP
    |--------------------------------------------
    */
    public function verifyOTP(int $userId, string $otp): bool
    {

        $stmt = $this->db->prepare("
            SELECT * FROM otp_verifications
            WHERE user_id = ?
            AND otp_code = ?
            AND expires_at > NOW()
            AND used = 0
            ORDER BY id DESC
            LIMIT 1
        ");

        $stmt->execute([$userId, $otp]);

        $record = $stmt->fetch();

        if (!$record) {
            return false;
        }

        // mark OTP as used
        $this->db->prepare("
            UPDATE otp_verifications
            SET used = 1, verified = 1
            WHERE id = ?
        ")->execute([$record['id']]);

        return true;
    }
}