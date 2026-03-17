<?php

namespace App\Controllers;

use PDO;
use Exception;
use App\Services\JwtService;
use App\Services\RedisService;
use App\Services\EmailService;
use App\Services\WhatsAppService;
class AuthController
{
    private PDO $db;
    public JwtService $jwtService;
    private RedisService $redis;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->jwtService = new JwtService($db);
        $this->redis = new RedisService();
    }

    /*
    |--------------------------------------------------------------------------
    | REGISTER
    |--------------------------------------------------------------------------
    */
    public function register(array $data): array
{
    try {

        if (empty($data['phone']) || empty($data['password'])) {
            return [
                'success' => false,
                'message' => 'Phone and password are required'
            ];
        }

        $this->db->beginTransaction();

        // Check if phone exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE phone = ?");
        $stmt->execute([$data['phone']]);

        if ($stmt->fetch()) {
            return [
                'success' => false,
                'message' => 'Phone already registered'
            ];
        }

        $passwordHash = password_hash(
            $data['password'],
            PASSWORD_BCRYPT,
            ['cost' => (int)($_ENV['PASSWORD_HASH_COST'] ?? 12)]
        );

        // Insert user
        $stmt = $this->db->prepare("
            INSERT INTO users
            (name, display_name, phone, email, password_hash, gender, religion, tribe, date_of_birth,
             continent, country, city, relationship_intent, user_type, role, is_verified, is_active, created_at, updated_at)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,0,1,NOW(),NOW())
        ");

        $stmt->execute([
            $data['name'] ?? null,
            $data['name'] ?? null,
            $data['phone'],
            $data['email'] ?? null,
            $passwordHash,
            $data['gender'] ?? null,
            $data['religion'] ?? null,
            $data['tribe'] ?? null,
            $data['date_of_birth'] ?? null,
            $data['continent'] ?? null,
            $data['country'] ?? null,
            $data['city'] ?? null,
            $data['relationship_intent'] ?? null,
            $data['user_type'] ?? 'normal',
            'user'
        ]);

        $userId = (int)$this->db->lastInsertId();

        // Create profile
        $stmt = $this->db->prepare("
            INSERT INTO profiles (user_id, gender, country, is_premium, created_at, updated_at)
            VALUES (?,?,?,?,NOW(),NOW())
        ");

        $stmt->execute([
            $userId,
            $data['gender'] ?? null,
            $data['country'] ?? null,
            0
        ]);

        $this->db->commit();

        // 🔑 Generate tokens
        $accessToken = $this->jwtService->generateAccessToken($userId);
        $refreshToken = $this->jwtService->generateRefreshToken($userId);
        
        return [
            'success' => true,
            'message' => 'User registered successfully',
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'user' => [
                'id' => $userId,
                'phone' => $data['phone']
            ]
        ];

    } catch (\Exception $e) {

        $this->db->rollBack();

        return [
            'success' => false,
            'message' => 'Registration failed',
            'error' => $e->getMessage()
        ];
    }
}

    /*
    |--------------------------------------------------------------------------
    | LOGIN
    |--------------------------------------------------------------------------
    */
    public function login(array $data): array
    {
        try {
            if (empty($data['phone']) || empty($data['password'])) {
                return [
                    'success' => false,
                    'message' => 'Phone and password are required'
                ];
            }

            $cacheKey = "login_user:" . $data['phone'];

            // Check Redis cache
            $user = $this->redis->exists($cacheKey)
                ? $this->redis->get($cacheKey)
                : null;

            // If not cached, fetch from DB
            if (!$user) {
                $stmt = $this->db->prepare("
                    SELECT id,password_hash,is_active 
                    FROM users 
                    WHERE phone = ?
                ");
                $stmt->execute([$data['phone']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    $this->redis->set($cacheKey, $user, 300);
                }
            }

            // Validate credentials
            if (!$user || !password_verify($data['password'], $user['password_hash'])) {
                return [
                    'success' => false,
                    'message' => 'Invalid credentials'
                ];
            }

            if (!$user['is_active']) {
                return [
                    'success' => false,
                    'message' => 'Account disabled'
                ];
            }

            // Generate tokens
            $accessToken = $this->jwtService->generateAccessToken((int)$user['id']);
            $refreshToken = $this->jwtService->generateRefreshToken((int)$user['id']);
            
            // Check if profile already exists
            $stmt = $this->db->prepare("
                SELECT user_id FROM profiles WHERE user_id = ?
            ");
            $stmt->execute([$user['id']]);
            $hasProfile = $stmt->fetch() ? true : false;      
            // Cache session in Redis
            $this->redis->set(
                "session_user:" . $user['id'],
                [
                    'phone' => $data['phone'],
                    'access_token' => $accessToken
                ],
                (int)($_ENV['JWT_ACCESS_TTL'] ?? 900)
            );

            return [
                'success' => true,
                'message' => 'Login successful',
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'has_profile' =>$hasProfile,
                'user' => [
                    'id' => $user['id'],
                    'phone' => $data['phone']
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ];
        }
    }
    
    //request regotp
    public function requestRegistrationOTP(array $data): array
{
    try {
        if (empty($data['email'])) {
            return [
                'success' => false,
                'message' => 'Email is required'
            ];
        }

        $email = $data['email'];

        // Generate secure OTP
        $otp = random_int(100000, 999999);

        // Save OTP in database
        $stmt = $this->db->prepare("
            INSERT INTO otp_verifications
            (identifier, otp_code, expires_at, created_at)
            VALUES (?,?,DATE_ADD(NOW(), INTERVAL 10 MINUTE),NOW())
        ");
        $stmt->execute([$email, $otp]);

        // Send email & log to error_log
        $emailService = new EmailService();

        if ($emailService->sendOTP($email, $otp)) {
            error_log("OTP sent to {$email}: {$otp}");
        } else {
            error_log("Failed to send OTP to {$email}: {$otp}");
            return [
                'success' => false,
                'message' => 'Failed to send OTP email'
            ];
        }

        return [
            'success' => true,
            'message' => 'OTP sent to email successfully'
        ];

    } catch (Exception $e) {
        error_log("OTP request error for {$data['email']}: " . $e->getMessage());

        return [
            'success' => false,
            'message' => 'Failed to send OTP',
            'error' => $e->getMessage()
        ];
    }
}

    //verify regotp
    public function verifyRegistrationOTP(array $data): array
{
    try {

        if (empty($data['email']) || empty($data['otp'])) {
            return [
                'success' => false,
                'message' => 'Email and OTP required'
            ];
        }

        $stmt = $this->db->prepare("
            SELECT * FROM otp_verifications
            WHERE identifier = ?
            AND otp_code = ?
            AND used = 0
            AND expires_at > NOW()
            ORDER BY id DESC
            LIMIT 1
        ");

        $stmt->execute([
            $data['email'],
            $data['otp']
        ]);

        $otp = $stmt->fetch();

        if (!$otp) {
            return [
                'success' => false,
                'message' => 'Invalid or expired OTP'
            ];
        }

        $stmt = $this->db->prepare("
            UPDATE otp_verifications
            SET used = 1, verified = 1
            WHERE id = ?
        ");

        $stmt->execute([$otp['id']]);

        return [
            'success' => true,
            'message' => 'OTP verified'
        ];

    } catch (Exception $e) {

        return [
            'success' => false,
            'message' => 'OTP verification failed',
            'error' => $e->getMessage()
        ];
    }
}


    /*
    |--------------------------------------------------------------------------
    | VERIFY OTP (stub for now)
    |--------------------------------------------------------------------------
    */
    public function verifyOtp(int $userId, string $otpCode): array
    {
        // Later you can connect this to a real SMS OTP service
        return [
            'success' => true,
            'message' => 'OTP verified successfully'
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | REFRESH TOKEN
    |--------------------------------------------------------------------------
    */
    public function refresh(string $refreshToken): array
    {
        $userId = $this->jwtService->validateRefreshToken($refreshToken);

        if (!$userId) {
            return [
                'success' => false,
                'message' => 'Invalid refresh token'
            ];
        }

        $newAccessToken = $this->jwtService->generateAccessToken($userId);

        // Update Redis session
        $this->redis->set("session_user:" . $userId, [
            'access_token' => $newAccessToken
        ], (int)($_ENV['JWT_ACCESS_TTL'] ?? 900));

        return [
            'success' => true,
            'access_token' => $newAccessToken,
            'expires_in' => (int)($_ENV['JWT_ACCESS_TTL'] ?? 900)
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */
    public function logout(string $refreshToken): array
    {
        $userId = $this->jwtService->validateRefreshToken($refreshToken);

        if ($userId) {
            $this->jwtService->revokeRefreshToken($refreshToken);
            $this->redis->delete("session_user:" . $userId);
        }

        return [
            'success' => true,
            'message' => 'Logged out successfully'
        ];
    }
}