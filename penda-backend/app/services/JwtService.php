<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PDO;
use Exception;

class JwtService
{
    private string $secret;
    private string $algo;
    private int $accessTtl;
    private int $refreshTtl;
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->secret = $_ENV['JWT_SECRET'];
        $this->algo = $_ENV['JWT_ALGO'] ?? 'HS256';
        $this->accessTtl = (int) ($_ENV['JWT_TTL'] ?? 3600); // Access token TTL in seconds
        $this->refreshTtl = (int) ($_ENV['JWT_REFRESH_TTL'] ?? 86400); // Refresh token TTL in seconds
    }

    /*
    |----------------------------------------------------------------------
    | Generate Access Token
    |----------------------------------------------------------------------
    */
    public function generateAccessToken(int $userId): string
    {
        $payload = [
            'iss'  => $_ENV['JWT_ISSUER'] ?? 'pendaBackend',
            'aud'  => $_ENV['JWT_AUDIENCE'] ?? 'pendaMobileApp',
            'iat'  => time(),
            'exp'  => time() + $this->accessTtl,
            'sub'  => $userId,
            'type' => 'access'
        ];

        return JWT::encode($payload, $this->secret, $this->algo);
    }

    /*
    |----------------------------------------------------------------------
    | Generate Refresh Token
    | Stored hashed in DB for security
    |----------------------------------------------------------------------
    */
    public function generateRefreshToken(int $userId): string
    {
        $refreshToken = bin2hex(random_bytes(64));
        $hashedToken = hash('sha256', $refreshToken);

        $stmt = $this->db->prepare("
            INSERT INTO refresh_tokens (user_id, token_hash, expires_at, revoked, created_at)
            VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND), 0, NOW())
        ");

        $stmt->execute([
            $userId,
            $hashedToken,
            $this->refreshTtl
        ]);

        return $refreshToken;
    }

    /*
    |----------------------------------------------------------------------
    | Validate Access Token
    | Throws Exception if invalid/expired
    | Returns decoded payload object
    |----------------------------------------------------------------------
    */
    public function validateAccessToken(string $token): object
    {
        try {
            return JWT::decode($token, new Key($this->secret, $this->algo));
        } catch (\Throwable $e) {
            throw new Exception("Invalid or expired access token: " . $e->getMessage());
        }
    }

    /*
    |----------------------------------------------------------------------
    | Validate Refresh Token
    | Returns user_id if valid, null otherwise
    |----------------------------------------------------------------------
    */
    public function validateRefreshToken(string $refreshToken): ?int
    {
        $hashed = hash('sha256', $refreshToken);

        $stmt = $this->db->prepare("
            SELECT user_id 
            FROM refresh_tokens
            WHERE token_hash = ?
            AND revoked = 0
            AND expires_at > NOW()
            LIMIT 1
        ");

        $stmt->execute([$hashed]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? (int) $row['user_id'] : null;
    }

    /*
    |----------------------------------------------------------------------
    | Revoke Refresh Token
    | Sets revoked flag to 1
    |----------------------------------------------------------------------
    */
    public function revokeRefreshToken(string $refreshToken): void
    {
        $hashed = hash('sha256', $refreshToken);

        $stmt = $this->db->prepare("
            UPDATE refresh_tokens
            SET revoked = 1
            WHERE token_hash = ?
        ");

        $stmt->execute([$hashed]);
    }

    /*
    |----------------------------------------------------------------------
    | Optional: Decode JWT without verifying exp (for refresh)
    |----------------------------------------------------------------------
    */
    public function decodeWithoutExpiration(string $token): object
    {
        return JWT::decode($token, new Key($this->secret, $this->algo), ['HS256']);
    }

    public function getUserFromHeaders(array $headers): array
    {
        $headers = array_change_key_case($headers, CASE_LOWER);

if (!isset($headers['authorization'])) {
    throw new \Exception("Authorization header missing");
}

$token = str_replace('Bearer ', '', $headers['authorization']);
        try {

            $decoded = \Firebase\JWT\JWT::decode(
                $token,
                new \Firebase\JWT\Key($_ENV['JWT_SECRET'], 'HS256')
            );

            return [
                'id' => $decoded->sub
            ];

        } catch (\Exception $e) {

            throw new \Exception("Invalid token: " . $e->getMessage());
        }
    }
}
