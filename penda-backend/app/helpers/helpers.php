<?php

namespace App\Helpers;

class Helpers
{
    /**
     * Generate random numeric OTP
     */
    public static function generateOtp(int $length = 6): string
    {
        $otp = '';
        for ($i = 0; $i < $length; $i++) {
            $otp .= random_int(0, 9);
        }
        return $otp;
    }

    /**
     * Hash password using bcrypt
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * Verify password
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Generate random string (e.g., for tokens)
     */
    public static function randomString(int $length = 32): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * JSON Response helper
     */
    public static function jsonResponse($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Validate Email
     */
    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Sanitize string input
     */
    public static function sanitize(string $input): string
    {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Calculate age from date of birth
     */
    public static function calculateAge(string $dob): int
    {
        $birthDate = new \DateTime($dob);
        $today = new \DateTime();
        return $today->diff($birthDate)->y;
    }
}
