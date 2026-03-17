<?php
require_once __DIR__ . '/../../TestCase.php';
use App\Models\User;
use App\Services\AuthService;
use App\Middleware\AdminMiddleware;

class AdminMiddlewareTest extends TestCase
{
    protected User $userModel;
    protected AuthService $authService;
    protected AdminMiddleware $adminMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userModel = new User();
        $this->authService = new AuthService();
        $this->adminMiddleware = new AdminMiddleware($this->authService);
    }

    public function testAllowsAdminUser(): void
    {
        $userId = $this->userModel->create([
            'phone'=>'+25471100001',
            'email'=>'admin@test.com',
            'password_hash'=>password_hash('adminpass', PASSWORD_BCRYPT),
            'role'=>'admin',
            'is_verified'=>true
        ]);

        $jwt = $this->authService->login('+25471100001','adminpass');
        $request = ['headers'=>['Authorization'=>"Bearer $jwt"]];

        $response = $this->adminMiddleware->handle($request, function($req){ return 'next_called'; });
        $this->assertEquals('next_called', $response);
    }

    public function testBlocksNonAdminUser(): void
    {
        $userId = $this->userModel->create([
            'phone'=>'+25471100002',
            'email'=>'user@test.com',
            'password_hash'=>password_hash('userpass', PASSWORD_BCRYPT),
            'role'=>'user',
            'is_verified'=>true
        ]);

        $jwt = $this->authService->login('+25471100002','userpass');
        $request = ['headers'=>['Authorization'=>"Bearer $jwt"]];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Admin access required');

        $this->adminMiddleware->handle($request, function($req){});
    }
}
