<?php
require_once __DIR__ . '/../../TestCase.php';
use App\Models\User;
use App\Models\Interaction;
use App\Models\Match;
use App\Services\MatchService;

class MatchServiceTest extends TestCase
{
    protected MatchService $matchService;
    protected User $userModel;
    protected Interaction $interactionModel;
    protected Match $matchModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->matchService = new MatchService();
        $this->userModel = new User();
        $this->interactionModel = new Interaction();
        $this->matchModel = new Match();
    }

    public function testReciprocalLikeCreatesMatch(): void
    {
        $user1 = $this->userModel->create([
            'phone' => '+25470100020',
            'email' => 'matchservice1@test.com',
            'password_hash' => password_hash('secure123', PASSWORD_BCRYPT)
        ]);

        $user2 = $this->userModel->create([
            'phone' => '+25470100021',
            'email' => 'matchservice2@test.com',
            'password_hash' => password_hash('secure123', PASSWORD_BCRYPT)
        ]);

        $this->interactionModel->create([
            'liker_id' => $user1,
            'liked_id' => $user2,
            'type' => 'like'
        ]);

        $match = $this->matchService->processInteraction($user2, $user1, 'like');
        $this->assertIsArray($match);
        $this->assertArrayHasKey('id', $match);

        $matchRecord = $this->matchModel->find($match['id']);
        $this->assertEquals(min($user1, $user2), $matchRecord['user1_id']);
        $this->assertEquals(max($user1, $user2), $matchRecord['user2_id']);
    }

    public function testNoMatchForSingleLike(): void
    {
        $user1 = $this->userModel->create([
            'phone' => '+25470100022',
            'email' => 'singlelike1@test.com',
            'password_hash' => password_hash('secure123', PASSWORD_BCRYPT)
        ]);

        $user2 = $this->userModel->create([
            'phone' => '+25470100023',
            'email' => 'singlelike2@test.com',
            'password_hash' => password_hash('secure123', PASSWORD_BCRYPT)
        ]);

        $result = $this->matchService->processInteraction($user1, $user2, 'like');
        $this->assertNull($result); // No match yet
    }
}
