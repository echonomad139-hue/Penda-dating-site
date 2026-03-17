<?php
require_once __DIR__ . '/../../TestCase.php';
use App\Models\User;
use App\Models\Profile;
use App\Models\Photo;

class ProfileTest extends TestCase
{
    public function testCreateProfileAndAttachPhotos(): void
    {
        $userModel = new User();
        $profileModel = new Profile();
        $photoModel = new Photo();

        $userId = $userModel->create([
            'phone' => '+254700000020',
            'email' => 'profile@test.com',
            'password_hash' => password_hash('Secret123!', PASSWORD_BCRYPT)
        ]);

        // Create profile
        $profile = $profileModel->create([
            'user_id' => $userId,
            'display_name' => 'ProfileUser',
            'date_of_birth' => '1992-03-10',
            'gender' => 'female',
            'bio' => 'Testing profile functionality',
            'city' => 'Nairobi'
        ]);
        $this->assertEquals($userId, $profile);

        // Add photos
        $photo1 = $photoModel->create([
            'user_id' => $userId,
            'url' => 'https://example.com/photo1.jpg',
            'is_primary' => true
        ]);
        $photo2 = $photoModel->create([
            'user_id' => $userId,
            'url' => 'https://example.com/photo2.jpg',
            'is_primary' => false
        ]);

        $photos = $photoModel->getByUserId($userId);
        $this->assertCount(2, $photos);
        $this->assertTrue((bool)$photos[0]['is_primary']);
    }

    public function testProfileAgeCalculation(): void
    {
        $profileModel = new Profile();
        $userId = rand(1000,9999);

        $profileModel->create([
            'user_id' => $userId,
            'display_name' => 'AgeTester',
            'date_of_birth' => '2000-01-01',
            'gender' => 'male'
        ]);

        $profile = $profileModel->find($userId);
        $dob = $profile['date_of_birth'];
        $age = (new DateTime())->diff(new DateTime($dob))->y;

        $this->assertEquals(23, $age); // Assuming current year is 2023
    }
}
