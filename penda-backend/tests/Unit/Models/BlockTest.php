<?php
require_once __DIR__ . '/../../TestCase.php';
use App\Models\User;
use App\Models\Block;

class BlockTest extends TestCase
{
    public function testBlockAndUnblockUser(): void
    {
        $userModel = new User();
        $blockModel = new Block();

        $blocker = $userModel->create([
            'phone' => '+25470000220',
            'email' => 'blocker@test.com',
            'password_hash' => password_hash('secret123', PASSWORD_BCRYPT)
        ]);

        $blocked = $userModel->create([
            'phone' => '+25470000221',
            'email' => 'blocked@test.com',
            'password_hash' => password_hash('secret123', PASSWORD_BCRYPT)
        ]);

        $blockId = $blockModel->create([
            'blocker_id' => $blocker,
            'blocked_id' => $blocked
        ]);

        $this->assertIsInt($blockId);

        // Attempt to create duplicate block (should fail)
        $this->expectException(PDOException::class);
        $blockModel->create([
            'blocker_id' => $blocker,
            'blocked_id' => $blocked
        ]);

        // Unblock
        $deleted = $blockModel->delete($blockId);
        $this->assertTrue($deleted);
    }

    public function testBlockPreventsInteraction(): void
    {
        $userModel = new User();
        $blockModel = new Block();
        $interactionModel = new \App\Models\Interaction();

        $user1 = $userModel->create([
            'phone' => '+25470000222',
            'email' => 'blocker2@test.com',
            'password_hash' => password_hash('secret123', PASSWORD_BCRYPT)
        ]);

        $user2 = $userModel->create([
            'phone' => '+25470000223',
            'email' => 'blocked2@test.com',
            'password_hash' => password_hash('secret123', PASSWORD_BCRYPT)
        ]);

        $blockModel->create([
            'blocker_id' => $user1,
            'blocked_id' => $user2
        ]);

        // Interaction should throw exception
        $this->expectException(Exception::class);
        $interactionModel->create([
            'liker_id' => $user2,
            'liked_id' => $user1,
            'type' => 'like'
        ]);
    }
}
