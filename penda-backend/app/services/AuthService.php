<?php

namespace App\Services;

use PDO;
use Exception;

class AuthService
{
    private PDO $db;
    private string $jwtSecret = "penda_SUPER_SECRET_KEY";

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /*
    |--------------------------------------------------------------------------
    | HASH PASSWORD
    |--------------------------------------------------------------------------
    */
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE JWT
    |--------------------------------------------------------------------------
    */
    public function generateJWT(array $user): string
    {
        $header = base64_encode(json_encode(['alg'=>'HS256','typ'=>'JWT']));
        $payload = base64_encode(json_encode([
            'user_id' => $user['id'],
            'phone' => $user['phone'],
            'role' => $user['role'],
            'exp' => time() + (60 * 60 * 24) // 24h
        ]));

        $signature = hash_hmac(
            'sha256',
            "$header.$payload",
            $this->jwtSecret,
            true
        );

        $signature = base64_encode($signature);

        return "$header.$payload.$signature";
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDATE JWT
    |--------------------------------------------------------------------------
    */
    public function validateJWT(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;

        [$header, $payload, $signature] = $parts;

        $validSignature = base64_encode(
            hash_hmac('sha256', "$header.$payload", $this->jwtSecret, true)
        );

        if (!hash_equals($validSignature, $signature)) {
            return null;
        }

        $decoded = json_decode(base64_decode($payload), true);

        if ($decoded['exp'] < time()) {
            return null;
        }

        return $decoded;
    }

    /*
    |--------------------------------------------------------------------------
    | AUTHENTICATE USER
    |--------------------------------------------------------------------------
    */
    public function authenticate(string $phone, string $password): array
    {
        $stmt = $this->db->prepare("
            SELECT id, phone, password_hash, is_verified, is_active, role
            FROM users WHERE phone = ?
        ");
        $stmt->execute([$phone]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return ['status'=>false,'message'=>'Invalid credentials'];
        }

        if (!$user['is_active']) {
            return ['status'=>false,'message'=>'Account disabled'];
        }

        if (!$this->verifyPassword($password, $user['password_hash'])) {
            return ['status'=>false,'message'=>'Invalid credentials'];
        }

        $token = $this->generateJWT($user);

        return [
            'status'=>true,
            'token'=>$token,
            'user'=>$user
        ];
    }
}
