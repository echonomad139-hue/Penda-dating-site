<?php
require_once __DIR__ . '/../../TestCase.php';
use App\Models\User;
use App\Services\AuthService;
use App\Middleware\RateLimitMiddleware;

class RateLimitMiddlewareTest extends TestCase
{
    protected User $userModel;
    protected AuthService $authService;
    protected RateLimitMiddleware $rateLimitMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userModel = new User();
        $this->authService = new AuthService();
        $this->rateLimitMiddleware = new RateLimitMiddleware(5, 60); // 5 requests per 60 seconds
    }

    public function testAllowsRequestsUnderLimit(): void
    {
        $userId = $this->userModel->create([
            'phone'=>'+25471200001',
            'email'=>'ratelimit@test.com',
            'password_hash'=>password_hash('pass123', PASSWORD_BCRYPT),
            'is_verified'=>true
        ]);

        for ($i=0; $i<5; $i++) {
            $response = $this->rateLimitMiddleware->handle(['user_id'=>$userId], function($req){ return 'next_called'; });
            $this->assertEquals('next_called', $response);
        }
    }

    public function testBlocksRequestsOverLimit(): void
    {
        $userId = $this->userModel->create([
            'phone'=>'+25471200002',
            'email'=>'ratelimit2@test.com',
            'password_hash'=>password_hash('pass123', PASSWORD_BCRYPT),
            'is_verified'=>true
        ]);

        // hit 5 requests
        for ($i=0;$i<5;$i++){
            $this->rateLimitMiddleware->handle(['user_id'=>$userId], function($req){ return 'next_called'; });
        }

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Rate limit exceeded');

        $this->rateLimitMiddleware->handle(['user_id'=>$userId], function($req){});
    }
}
