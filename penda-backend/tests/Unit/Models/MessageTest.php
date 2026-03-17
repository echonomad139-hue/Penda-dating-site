<?php
require_once __DIR__ . '/../../TestCase.php';
use App\Models\User;
use App\Models\Match;
use App\Models\Message;

class MessageTest extends TestCase
{
    public function testSendAndReadMessage(): void
    {
        $userModel = new User();
        $matchModel = new Match();
        $messageModel = new Message();

        $user1 = $userModel->create([
            'phone' => '+25470000120',
            'email' => 'sender@test.com',
            'password_hash' => password_hash('pass123', PASSWORD_BCRYPT)
        ]);

        $user2 = $userModel->create([
            'phone' => '+25470000121',
            'email' => 'receiver@test.com',
            'password_hash' => password_hash('pass123', PASSWORD_BCRYPT)
        ]);

        $matchId = $matchModel->create([
            'user1_id' => min($user1, $user2),
            'user2_id' => max($user1, $user2)
        ]);

        // Send message
        $msgId = $messageModel->create([
            'match_id' => $matchId,
            'sender_id' => $user1,
            'body' => 'Hello, this is a test message'
        ]);

        $this->assertIsInt($msgId);

        // Read message
        $messageModel->markRead($msgId);
        $msg = $messageModel->find($msgId);

        $this->assertNotNull($msg['read_at']);
    }
}
