<?php
require_once __DIR__ . '/../../TestCase.php';
use App\Models\User;
use App\Models\Profile;
use App\Models\Photo;
use App\Models\Block;
use App\Services\DiscoverService;

class DiscoverServiceTest extends TestCase
{
    protected DiscoverService $discoverService;
    protected User $userModel;
    protected Profile $profileModel;
    protected Photo $photoModel;
    protected Block $blockModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->discoverService = new DiscoverService();
        $this->userModel = new User();
        $this->profileModel = new Profile();
        $this->photoModel = new Photo();
        $this->blockModel = new Block();
    }

    public function testGetDiscoverFeedExcludesBlocked(): void
    {
        $user1 = $this->userModel->create(['phone' => '+25470101000', 'email' => 'discuser1@test.com', 'password_hash' => password_hash('pass', PASSWORD_BCRYPT)]);
        $user2 = $this->userModel->create(['phone' => '+25470101001', 'email' => 'discuser2@test.com', 'password_hash' => password_hash('pass', PASSWORD_BCRYPT)]);
        $user3 = $this->userModel->create(['phone' => '+25470101002', 'email' => 'discuser3@test.com', 'password_hash' => password_hash('pass', PASSWORD_BCRYPT)]);

        $this->profileModel->create(['user_id'=>$user2,'display_name'=>'Alice','date_of_birth'=>'1995-01-01','gender'=>'female']);
        $this->profileModel->create(['user_id'=>$user3,'display_name'=>'Bob','date_of_birth'=>'1990-06-06','gender'=>'male']);

        $this->photoModel->create(['user_id'=>$user2,'url'=>'photo2.jpg','is_primary'=>true]);
        $this->photoModel->create(['user_id'=>$user3,'url'=>'photo3.jpg','is_primary'=>true]);

        // User1 blocks user3
        $this->blockModel->create(['blocker_id'=>$user1,'blocked_id'=>$user3]);

        $feed = $this->discoverService->getFeed($user1);
        $this->assertCount(1, $feed);
        $this->assertEquals($user2, $feed[0]['user_id']);
    }

    public function testFeedIncludesPrimaryPhotos(): void
    {
        $user1 = $this->userModel->create(['phone' => '+25470101003', 'email' => 'discuser4@test.com', 'password_hash' => password_hash('pass', PASSWORD_BCRYPT)]);
        $user2 = $this->userModel->create(['phone' => '+25470101004', 'email' => 'discuser5@test.com', 'password_hash' => password_hash('pass', PASSWORD_BCRYPT)]);

        $this->profileModel->create(['user_id'=>$user2,'display_name'=>'Carol','date_of_birth'=>'1992-02-02','gender'=>'female']);
        $this->photoModel->create(['user_id'=>$user2,'url'=>'carol.jpg','is_primary'=>true]);

        $feed = $this->discoverService->getFeed($user1);
        $this->assertEquals('carol.jpg', $feed[0]['primary_photo']);
    }
}
