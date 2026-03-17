<?php
require_once __DIR__ . '/../../TestCase.php';
use App\Models\User;
use App\Models\OtpVerification;

class AuthRoutesTest extends TestCase
{
    protected User $userModel;
    protected OtpVerification $otpModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userModel = new User();
        $this->otpModel = new OtpVerification();
    }

    public function testUserRegistrationEndpoint(): void
    {
        $response = $this->post('/api/auth/register', [
            'phone'=>'+25472000001',
            'email'=>'authroute@test.com',
            'password'=>'secure123'
        ]);

        $response->assertStatus(201);
        $user = $this->userModel->findByPhone('+25472000001');
        $this->assertEquals('authroute@test.com', $user['email']);
    }

    public function testLoginEndpointGeneratesJwt(): void
    {
        $userId = $this->userModel->create([
            'phone'=>'+25472000002',
            'email'=>'loginroute@test.com',
            'password_hash'=>password_hash('secure123', PASSWORD_BCRYPT),
            'is_verified'=>true
        ]);

        $response = $this->post('/api/auth/login', [
            'phone'=>'+25472000002',
            'password'=>'secure123'
        ]);

        $response->assertStatus(200);
        $this->assertArrayHasKey('token', $response->json());
    }

    public function testOtpVerificationEndpoint(): void
    {
        $userId = $this->userModel->create([
            'phone'=>'+25472000003',
            'email'=>'otproute@test.com',
            'password_hash'=>password_hash('secure123', PASSWORD_BCRYPT)
        ]);

        $otpCode = '123456';
        $this->otpModel->create([
            'user_id'=>$userId,
            'otp_code'=>$otpCode,
            'expires_at'=>date('Y-m-d H:i:s', strtotime('+10 minutes')),
            'verified'=>false
        ]);

        $response = $this->post('/api/auth/verify-otp', [
            'user_id'=>$userId,
            'otp_code'=>$otpCode
        ]);

        $response->assertStatus(200);
        $otpRecord = $this->otpModel->findLatestByUser($userId);
        $this->assertTrue((bool)$otpRecord['verified']);
    }
}
