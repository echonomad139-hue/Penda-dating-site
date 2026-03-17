<?php
require_once __DIR__ . '/../../TestCase.php';
use App\Models\User;
use App\Models\Profile;

class AdminRoutesTest extends TestCase
{
    protected User $userModel;
    protected Profile $profileModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userModel = new User();
        $this->profileModel = new Profile();
    }

    public function testModerateProfile(): void
    {
        $admin = $this->userModel->create(['phone'=>'+25472600001','email'=>'admin@test.com','password_hash'=>password_hash('pass', PASSWORD_BCRYPT),'role'=>'admin','is_verified'=>true]);
        $userId = $this->userModel->create(['phone'=>'+25472600002','email'=>'user@test.com','password_hash'=>password_hash('pass', PASSWORD_BCRYPT)]);

        $this->profileModel->create([
            'user_id'=>$userId,
            'display_name'=>'TestUser',
            'date_of_birth'=>'1990-01-01',
            'gender'=>'male',
            'bio'=>'Some bio'
        ]);

        $response = $this->put("/api/admin/moderate/$userId", ['action'=>'suspend']);
        $response->assertStatus(200);

        $profile = $this->profileModel->find($userId);
        $this->assertEquals('suspended', $profile['status'] ?? 'active');
    }

    public function testAdminDashboard(): void
    {
        $admin = $this->userModel->create(['phone'=>'+25472600003','email'=>'admindash@test.com','password_hash'=>password_hash('pass', PASSWORD_BCRYPT),'role'=>'admin','is_verified'=>true]);

        $response = $this->get("/api/admin/dashboard");
        $response->assertStatus(200);
        $this->assertArrayHasKey('total_users', $response->json());
        $this->assertArrayHasKey('active_matches', $response->json());
    }
}
