<?php
require_once __DIR__ . '/../../TestCase.php';
use App\Models\User;
use App\Models\Profile;

class ProfileRoutesTest extends TestCase
{
    protected User $userModel;
    protected Profile $profileModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userModel = new User();
        $this->profileModel = new Profile();
    }

    public function testGetProfileEndpoint(): void
    {
        $userId = $this->userModel->create([
            'phone'=>'+25472100001',
            'email'=>'profileget@test.com',
            'password_hash'=>password_hash('pass123', PASSWORD_BCRYPT)
        ]);

        $this->profileModel->create([
            'user_id'=>$userId,
            'display_name'=>'Malik',
            'date_of_birth'=>'1995-05-03',
            'gender'=>'male'
        ]);

        $response = $this->get("/api/profile/$userId");
        $response->assertStatus(200);
        $this->assertEquals('Malik', $response->json()['display_name']);
    }

    public function testUpdateProfileEndpoint(): void
    {
        $userId = $this->userModel->create([
            'phone'=>'+25472100002',
            'email'=>'profileupdate@test.com',
            'password_hash'=>password_hash('pass123', PASSWORD_BCRYPT)
        ]);

        $this->profileModel->create([
            'user_id'=>$userId,
            'display_name'=>'OldName',
            'date_of_birth'=>'1995-01-01',
            'gender'=>'male'
        ]);

        $response = $this->put("/api/profile/$userId", [
            'display_name'=>'NewName',
            'bio'=>'Updated bio here'
        ]);

        $response->assertStatus(200);
        $profile = $this->profileModel->find($userId);
        $this->assertEquals('NewName', $profile['display_name']);
        $this->assertEquals('Updated bio here', $profile['bio']);
    }
}
