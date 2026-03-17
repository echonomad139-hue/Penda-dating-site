<?php

namespace App\Controllers;

use PDO;
use App\Services\OtpService;
use App\Services\EmailService;
use App\Services\SmsService;

class PasswordController
{
    private PDO $db;
    private OtpService $otpService;
    private EmailService $emailService;
    private SmsService $smsService;

    public function __construct(PDO $db)
    {
        $this->db = $db;

        $this->otpService = new OtpService($db);
        $this->emailService = new EmailService();
        $this->smsService = new SmsService($db);
    }

    /*
    |--------------------------------------------
    | REQUEST OTP
    |--------------------------------------------
    */

    public function requestOTP(array $data): array
    {
        $method = $data['method'] ?? null;
        $email = $data['email'] ?? null;
        $phone = $data['phone'] ?? null;

        if ($method === 'email' && empty($email)) {
            return [
                'success' => false,
                'message' => 'Email is required'
            ];
        }

        if ($method === 'phone' && empty($phone)) {
            return [
                'success' => false,
                'message' => 'Phone number is required'
            ];
        }

        if ($method === 'email') {

            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);

        } else {

            $stmt = $this->db->prepare("SELECT id FROM users WHERE phone = ?");
            $stmt->execute([$phone]);

        }

        $user = $stmt->fetch();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }

        $otp = $this->otpService->generateOTP($user['id']);

        if ($method === 'email') {

            $this->emailService->sendOTP($email, $otp);

        } else {

            $this->smsService->sendOtp($phone, $otp);

        }

        return [
            'success' => true,
            'message' => 'OTP sent successfully'
        ];
    }

    /*
    |--------------------------------------------
    | VERIFY OTP
    |--------------------------------------------
    */

    public function verifyOTP(array $data): array
    {
        if (!isset($data['identifier'], $data['otp'])) {
            return [
                'success' => false,
                'message' => 'Identifier and OTP required'
            ];
        }

        $stmt = $this->db->prepare("
            SELECT id FROM users
            WHERE email = ? OR phone = ?
        ");

        $stmt->execute([
            $data['identifier'],
            $data['identifier']
        ]);

        $user = $stmt->fetch();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }

        $valid = $this->otpService->verifyOTP($user['id'], $data['otp']);

        if (!$valid) {
            return [
                'success' => false,
                'message' => 'Invalid or expired OTP'
            ];
        }

        return [
            'success' => true,
            'message' => 'OTP verified'
        ];
    }

    /*
    |--------------------------------------------
    | RESET PASSWORD
    |--------------------------------------------
    */

    public function resetPassword(array $data): array
    {
        if (!isset($data['identifier'], $data['newPassword'])) {
            return [
                'success' => false,
                'message' => 'Missing fields'
            ];
        }

        $stmt = $this->db->prepare("
            SELECT id FROM users
            WHERE email = ? OR phone = ?
        ");

        $stmt->execute([
            $data['identifier'],
            $data['identifier']
        ]);

        $user = $stmt->fetch();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }

        $password = password_hash($data['newPassword'], PASSWORD_BCRYPT);

        $this->db->prepare("
            UPDATE users
            SET password_hash = ?, updated_at = NOW()
            WHERE id = ?
        ")->execute([
            $password,
            $user['id']
        ]);

        return [
            'success' => true,
            'message' => 'Password reset successful'
        ];
    }
}