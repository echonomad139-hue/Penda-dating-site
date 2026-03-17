<?php
require_once __DIR__ . '/../../TestCase.php';

use App\Models\User;
use App\Models\Profile;
use App\Models\Photo;
use App\Models\Interaction;
use App\Models\Match;
use App\Models\Message;
use App\Models\OtpVerification;

class UserFlowTest extends TestCase
{
    protected User $userModel;
    protected Profile $profileModel;
    protected Photo $photoModel;
    protected Interaction $interactionModel;
    protected Match $matchModel;
    protected Message $messageModel;
    protected OtpVerification $otpModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userModel = new User();
        $this->profileModel = new Profile();
        $this->photoModel = new Photo();
        $this->interactionModel = new Interaction();
        $this->matchModel = new Match();
        $this->messageModel = new Message();
        $this->otpModel = new OtpVerification();
    }

    public function testFullUserFlow(): void
    {
        // 1️⃣ Registration & OTP
        $user1Id = $this->userModel->create([
            'phone'=>'+25473000001',
            'email'=>'userflow1@test.com',
            'password_hash'=>password_hash('secure123', PASSWORD_BCRYPT)
        ]);
        $otpCode = '654321';
        $this->otpModel->create([
            'user_id'=>$user1Id,
            'otp_code'=>$otpCode,
            'expires_at'=>date('Y-m-d H:i:s', strtotime('+10 minutes'))
        ]);
        $this->otpModel->verify($user1Id, $otpCode);
        $user1 = $this->userModel->find($user1Id);
        $this->assertTrue((bool)$user1['is_verified']);

        // 2️⃣ Profile setup
        $this->profileModel->create([
            'user_id'=>$user1Id,
            'display_name'=>'Malik',
            'date_of_birth'=>'1995-05-03',
            'gender'=>'male',
            'bio'=>'Just testing the app flow',
            'city'=>'Nyeri',
            'country'=>'Kenya'
        ]);

        $profile = $this->profileModel->find($user1Id);
        $this->assertEquals('Malik', $profile['display_name']);

        // 3️⃣ Photo upload
        $photoId = $this->photoModel->create([
            'user_id'=>$user1Id,
            'url'=>'https://cloudinary.com/malik/photo1.jpg',
            'is_primary'=>true
        ]);
        $photo = $this->photoModel->find($photoId);
        $this->assertTrue((bool)$photo['is_primary']);

        // 4️⃣ Interaction & Match
        $user2Id = $this->userModel->create([
            'phone'=>'+25473000002',
            'email'=>'userflow2@test.com',
            'password_hash'=>password_hash('secure123', PASSWORD_BCRYPT),
            'is_verified'=>true
        ]);

        $this->interactionModel->create(['liker_id'=>$user1Id,'liked_id'=>$user2Id,'type'=>'like']);
        $this->interactionModel->create(['liker_id'=>$user2Id,'liked_id'=>$user1Id,'type'=>'like']);

        $matchId = $this->matchModel->create([
            'user1_id'=>min($user1Id,$user2Id),
            'user2_id'=>max($user1Id,$user2Id)
        ]);
        $match = $this->matchModel->find($matchId);
        $this->assertEquals($matchId, $match['id']);

        // 5️⃣ Messaging
        $messageId = $this->messageModel->create([
            'match_id'=>$matchId,
            'sender_id'=>$user1Id,
            'body'=>'Hello, nice to meet you!'
        ]);
        $message = $this->messageModel->find($messageId);
        $this->assertEquals('Hello, nice to meet you!', $message['body']);
    }
}
