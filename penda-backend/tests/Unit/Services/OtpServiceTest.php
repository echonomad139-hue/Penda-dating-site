<?php
require_once __DIR__ . '/../../TestCase.php';
use App\Models\User;
use App\Models\OtpVerification;
use App\Services\OtpService;

class OtpServiceTest extends TestCase
{
    protected OtpService $otpService;
    protected User $userModel;
    protected OtpVerification $otpModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->otpService = new OtpService();
        $this->userModel = new User();
        $this->otpModel = new OtpVerification();
    }

    public function testOtpGenerationAndExpiration(): void
    {
        $userId = $this->userModel->create([
            'phone' => '+25470100010',
            'email' => 'otpuser@test.com',
            'password_hash' => password_hash('secure123', PASSWORD_BCRYPT)
        ]);

        $otp = $this->otpService->generateOtp($userId, 1); // 1 min expiry
        $this->assertIsString($otp);

        $otpRecord = $this->otpModel->findLatestByUser($userId);
        $this->assertEquals($otp, $otpRecord['otp_code']);

        // Simulate expiration
        $this->otpModel->expireAllForUser($userId);
        $otpRecord = $this->otpModel->find($otpRecord['id']);
        $this->assertTrue(strtotime($otpRecord['expires_at']) < time());
    }

    public function testVerifyExpiredOtpFails(): void
    {
        $userId = $this->userModel->create([
            'phone' => '+25470100011',
            'email' => 'expiredotp@test.com',
            'password_hash' => password_hash('secure123', PASSWORD_BCRYPT)
        ]);

        $otp = $this->otpService->generateOtp($userId, -1); // already expired
        $verified = $this->otpService->verifyOtp($userId, $otp);
        $this->assertFalse($verified);
    }
}
