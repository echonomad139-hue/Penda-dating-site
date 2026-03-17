<?php
require_once __DIR__ . '/../../TestCase.php';
use App\Models\User;
use App\Services\AuthService;
use App\Middleware\AuthMiddleware;

class AuthMiddlewareTest extends TestCase
{
    protected User $userModel;
    protected AuthService $authService;
    protected AuthMiddleware $authMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userModel = new User();
        $this->authService = new AuthService();
        $this->authMiddleware = new AuthMiddleware($this->authService);
    }

    public function testMiddlewareAllowsVerifiedUser(): void
    {
        $userId = $this->userModel->create([
            'phone'=>'+25471000001',
            'email'=>'authmid@test.com',
            'password_hash'=>password_hash('pass123', PASSWORD_BCRYPT),
            'is_verified'=>true,
            'is_active'=>true
        ]);

        $jwt = $this->authService->login('+25471000001', 'pass123');

        $request = ['headers'=>['Authorization'=>"Bearer $jwt"]];
        $response = $this->authMiddleware->handle($request, function($req){ return 'next_called'; });

        $this->assertEquals('next_called', $response);
    }

    public function testMiddlewareBlocksUnverifiedUser(): void
    {
        $userId = $this->userModel->create([
            'phone'=>'+25471000002',
            'email'=>'authmid2@test.com',
            'password_hash'=>password_hash('pass123', PASSWORD_BCRYPT),
            'is_verified'=>false,
            'is_active'=>true
        ]);

        $jwt = $this->authService->generateJwt($userId);

        $request = ['headers'=>['Authorization'=>"Bearer $jwt"]];
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User not verified');

        $this->authMiddleware->handle($request, function($req){});
    }
}
