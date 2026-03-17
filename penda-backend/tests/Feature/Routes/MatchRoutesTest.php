<?php
require_once __DIR__ . '/../../TestCase.php';
use App\Models\User;
use App\Models\Match;
use App\Models\Interaction;

class MatchRoutesTest extends TestCase
{
    protected User $userModel;
    protected Match $matchModel;
    protected Interaction $interactionModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userModel = new User();
        $this->matchModel = new Match();
        $this->interactionModel = new Interaction();
    }

    public function testGetMatchesEndpoint(): void
    {
        $user1 = $this->userModel->create(['phone'=>'+25472200001','email'=>'match1@test.com','password_hash'=>password_hash('pass', PASSWORD_BCRYPT)]);
        $user2 = $this->userModel->create(['phone'=>'+25472200002','email'=>'match2@test.com','password_hash'=>password_hash('pass', PASSWORD_BCRYPT)]);

        // Reciprocal like → match
        $this->interactionModel->create(['liker_id'=>$user1,'liked_id'=>$user2,'type'=>'like']);
        $this->interactionModel->create(['liker_id'=>$user2,'liked_id'=>$user1,'type'=>'like']);

        $match = $this->matchModel->create(['user1_id'=>min($user1,$user2),'user2_id'=>max($user1,$user2)]);

        $response = $this->get("/api/match/$user1");
        $response->assertStatus(200);
        $this->assertEquals($match, $response->json()[0]);
    }

    public function testUnmatchEndpoint(): void
    {
        $user1 = $this->userModel->create(['phone'=>'+25472200003','email'=>'unmatch1@test.com','password_hash'=>password_hash('pass', PASSWORD_BCRYPT)]);
        $user2 = $this->userModel->create(['phone'=>'+25472200004','email'=>'unmatch2@test.com','password_hash'=>password_hash('pass', PASSWORD_BCRYPT)]);

        $matchId = $this->matchModel->create(['user1_id'=>min($user1,$user2),'user2_id'=>max($user1,$user2)]);

        $response = $this->delete("/api/match/$user1/$user2");
        $response->assertStatus(200);

        $this->assertNull($this->matchModel->find($matchId));
    }
}
