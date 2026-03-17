<?php
require_once __DIR__ . '/../../TestCase.php';
use App\Models\User;
use App\Services\AuthService;
use App\Models\OtpVerification;

class AuthServiceTest extends TestCase
{
    protected AuthService $authService;
    protected User $userModel;
    protected OtpVerification $otpModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService();
        $this->userModel = new User();
        $this->otpModel = new OtpVerification();
    }

    public function testRegisterAndVerifyUser(): void
    {
        $userId = $this->userModel->create([
            'phone' => '+25470100000',
            'email' => 'authuser@test.com',
            'password_hash' => password_hash('secure123', PASSWORD_BCRYPT)
        ]);

        $this->assertIsInt($userId);

        // Generate OTP
        $otpCode = $this->authService->generateOtp($userId, 10); // 10 min expiry
        $otpRecord = $this->otpModel->findLatestByUser($userId);

        $this->assertEquals($otpCode, $otpRecord['otp_code']);
        $this->assertFalse((bool)$otpRecord['verified']);

        // Verify OTP
        $verified = $this->authService->verifyOtp($userId, $otpCode);
        $this->assertTrue($verified);

        $otpRecord = $this->otpModel->find($otpRecord['id']);
        $this->assertTrue((bool)$otpRecord['verified']);

        $user = $this->userModel->find($userId);
        $this->assertTrue((bool)$user['is_verified']);
    }

    public function testLoginGeneratesJwt(): void
    {
        $userId = $this->userModel->create([
            'phone' => '+25470100001',
            'email' => 'loginuser@test.com',
            'password_hash' => password_hash('secure123', PASSWORD_BCRYPT),
            'is_verified' => true
        ]);

        $jwt = $this->authService->login('+25470100001', 'secure123');
        $this->assertIsString($jwt);
        $this->assertStringContainsString('.', $jwt); // JWT format
    }
}
