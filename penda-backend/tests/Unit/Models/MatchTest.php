<?php
require_once __DIR__ . '/../../TestCase.php';
use App\Models\User;
use App\Models\Match;
use App\Models\Interaction;

class MatchTest extends TestCase
{
    public function testCreateAndRetrieveMatch(): void
    {
        $userModel = new User();
        $matchModel = new Match();

        $user1 = $userModel->create([
            'phone' => '+25470000110',
            'email' => 'match1@test.com',
            'password_hash' => password_hash('pass123', PASSWORD_BCRYPT)
        ]);

        $user2 = $userModel->create([
            'phone' => '+25470000111',
            'email' => 'match2@test.com',
            'password_hash' => password_hash('pass123', PASSWORD_BCRYPT)
        ]);

        // Always store smaller ID as user1_id
        $matchId = $matchModel->create([
            'user1_id' => min($user1, $user2),
            'user2_id' => max($user1, $user2)
        ]);

        $this->assertIsInt($matchId);

        $match = $matchModel->find($matchId);
        $this->assertEquals(min($user1, $user2), $match['user1_id']);
        $this->assertEquals(max($user1, $user2), $match['user2_id']);
    }

    public function testUnmatchRemovesRecord(): void
    {
        $userModel = new User();
        $matchModel = new Match();

        $user1 = $userModel->create([
            'phone' => '+25470000112',
            'email' => 'unmatch1@test.com',
            'password_hash' => password_hash('pass123', PASSWORD_BCRYPT)
        ]);

        $user2 = $userModel->create([
            'phone' => '+25470000113',
            'email' => 'unmatch2@test.com',
            'password_hash' => password_hash('pass123', PASSWORD_BCRYPT)
        ]);

        $matchId = $matchModel->create([
            'user1_id' => min($user1, $user2),
            'user2_id' => max($user1, $user2)
        ]);

        // Remove match
        $deleted = $matchModel->delete($matchId);
        $this->assertTrue($deleted);

        $this->assertNull($matchModel->find($matchId));
    }
}
