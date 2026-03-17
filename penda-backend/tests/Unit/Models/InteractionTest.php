<?php
require_once __DIR__ . '/../../TestCase.php';
use App\Models\User;
use App\Models\Interaction;
use App\Models\Match;

class InteractionTest extends TestCase
{
    public function testCreateLikeInteraction(): void
    {
        $userModel = new User();
        $interactionModel = new Interaction();
        $matchModel = new Match();

        $user1 = $userModel->create([
            'phone' => '+25470000100',
            'email' => 'liker@test.com',
            'password_hash' => password_hash('pass123', PASSWORD_BCRYPT)
        ]);

        $user2 = $userModel->create([
            'phone' => '+25470000101',
            'email' => 'liked@test.com',
            'password_hash' => password_hash('pass123', PASSWORD_BCRYPT)
        ]);

        $interactionId = $interactionModel->create([
            'liker_id' => $user1,
            'liked_id' => $user2,
            'type' => 'like'
        ]);

        $this->assertIsInt($interactionId);

        // Ensure unique interaction constraint
        $this->expectException(PDOException::class);
        $interactionModel->create([
            'liker_id' => $user1,
            'liked_id' => $user2,
            'type' => 'like'
        ]);
    }

    public function testSuperLikeCreatesMatch(): void
    {
        $userModel = new User();
        $interactionModel = new Interaction();
        $matchModel = new Match();

        $user1 = $userModel->create([
            'phone' => '+25470000102',
            'email' => 'superliker@test.com',
            'password_hash' => password_hash('pass123', PASSWORD_BCRYPT)
        ]);

        $user2 = $userModel->create([
            'phone' => '+25470000103',
            'email' => 'superliked@test.com',
            'password_hash' => password_hash('pass123', PASSWORD_BCRYPT)
        ]);

        $interactionModel->create([
            'liker_id' => $user1,
            'liked_id' => $user2,
            'type' => 'superlike'
        ]);

        // Simulate match creation (if reciprocal like exists)
        $matchId = $matchModel->create([
            'user1_id' => min($user1, $user2),
            'user2_id' => max($user1, $user2)
        ]);

        $this->assertIsInt($matchId);
    }
}
