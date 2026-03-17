<?php
require_once __DIR__ . '/../../TestCase.php';
use App\Models\User;
use App\Models\Match;
use App\Models\Message;
use App\Models\Interaction;

class MessageRoutesTest extends TestCase
{
    protected User $userModel;
    protected Match $matchModel;
    protected Message $messageModel;
    protected Interaction $interactionModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userModel = new User();
        $this->matchModel = new Match();
        $this->messageModel = new Message();
        $this->interactionModel = new Interaction();
    }

    public function testSendMessage(): void
    {
        $user1 = $this->userModel->create(['phone'=>'+25472300001','email'=>'msg1@test.com','password_hash'=>password_hash('pass', PASSWORD_BCRYPT)]);
        $user2 = $this->userModel->create(['phone'=>'+25472300002','email'=>'msg2@test.com','password_hash'=>password_hash('pass', PASSWORD_BCRYPT)]);

        // Reciprocal likes → match
        $this->interactionModel->create(['liker_id'=>$user1,'liked_id'=>$user2,'type'=>'like']);
        $this->interactionModel->create(['liker_id'=>$user2,'liked_id'=>$user1,'type'=>'like']);

        $matchId = $this->matchModel->create(['user1_id'=>min($user1,$user2),'user2_id'=>max($user1,$user2)]);

        $response = $this->post("/api/message/send", [
            'match_id'=>$matchId,
            'sender_id'=>$user1,
            'body'=>'Hello!'
        ]);

        $response->assertStatus(201);
        $message = $this->messageModel->findLatestByMatch($matchId);
        $this->assertEquals('Hello!', $message['body']);
    }

    public function testReadMessage(): void
    {
        $user1 = $this->userModel->create(['phone'=>'+25472300003','email'=>'msg3@test.com','password_hash'=>password_hash('pass', PASSWORD_BCRYPT)]);
        $user2 = $this->userModel->create(['phone'=>'+25472300004','email'=>'msg4@test.com','password_hash'=>password_hash('pass', PASSWORD_BCRYPT)]);

        $matchId = $this->matchModel->create(['user1_id'=>min($user1,$user2),'user2_id'=>max($user1,$user2)]);
        $msgId = $this->messageModel->create(['match_id'=>$matchId,'sender_id'=>$user1,'body'=>'Hey!']);

        $response = $this->get("/api/message/read/$msgId");
        $response->assertStatus(200);
        $this->assertNotNull($this->messageModel->find($msgId)['read_at']);
    }
}
