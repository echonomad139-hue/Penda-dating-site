<?php
require_once __DIR__ . '/../../TestCase.php';
use App\Models\User;
use App\Models\Photo;

class PhotoTest extends TestCase
{
    public function testUploadAndRetrievePhotos(): void
    {
        $userModel = new User();
        $photoModel = new Photo();

        $userId = $userModel->create([
            'phone' => '+254700000030',
            'email' => 'photo@test.com',
            'password_hash' => password_hash('Secret123!', PASSWORD_BCRYPT)
        ]);

        // Upload photos
        $photo1 = $photoModel->create([
            'user_id' => $userId,
            'url' => 'https://example.com/user1.jpg',
            'is_primary' => true
        ]);

        $photo2 = $photoModel->create([
            'user_id' => $userId,
            'url' => 'https://example.com/user2.jpg',
            'is_primary' => false
        ]);

        $photos = $photoModel->getByUserId($userId);
        $this->assertCount(2, $photos);
        $this->assertEquals($photos[0]['url'], 'https://example.com/user1.jpg');
    }

    public function testSetPrimaryPhoto(): void
    {
        $userModel = new User();
        $photoModel = new Photo();

        $userId = $userModel->create([
            'phone' => '+254700000031',
            'email' => 'primary@test.com',
            'password_hash' => password_hash('Secret123!', PASSWORD_BCRYPT)
        ]);

        $photoModel->create([
            'user_id' => $userId,
            'url' => 'https://example.com/old_primary.jpg',
            'is_primary' => true
        ]);

        $photoModel->create([
            'user_id' => $userId,
            'url' => 'https://example.com/new_primary.jpg',
            'is_primary' => false
        ]);

        // Set new primary
        $photoModel->setPrimary($userId, 'https://example.com/new_primary.jpg');

        $photos = $photoModel->getByUserId($userId);
        $primaryPhotos = array_filter($photos, fn($p) => $p['is_primary']);
        $this->assertCount(1, $primaryPhotos);
        $this->assertEquals(array_values($primaryPhotos)[0]['url'], 'https://example.com/new_primary.jpg');
    }
}
