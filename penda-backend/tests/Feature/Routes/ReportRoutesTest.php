<?php
require_once __DIR__ . '/../../TestCase.php';
use App\Models\User;
use App\Models\Report;
use App\Models\Block;

class ReportRoutesTest extends TestCase
{
    protected User $userModel;
    protected Report $reportModel;
    protected Block $blockModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userModel = new User();
        $this->reportModel = new Report();
        $this->blockModel = new Block();
    }

    public function testReportUser(): void
    {
        $reporter = $this->userModel->create(['phone'=>'+25472500001','email'=>'reporter@test.com','password_hash'=>password_hash('pass', PASSWORD_BCRYPT)]);
        $reported = $this->userModel->create(['phone'=>'+25472500002','email'=>'reported@test.com','password_hash'=>password_hash('pass', PASSWORD_BCRYPT)]);

        $response = $this->post("/api/report", [
            'reporter_id'=>$reporter,
            'reported_id'=>$reported,
            'reason'=>'spam',
            'description'=>'Sending unsolicited messages'
        ]);

        $response->assertStatus(201);
        $report = $this->reportModel->latest();
        $this->assertEquals('spam', $report['reason']);
    }

    public function testBlockUser(): void
    {
        $blocker = $this->userModel->create(['phone'=>'+25472500003','email'=>'blocker@test.com','password_hash'=>password_hash('pass', PASSWORD_BCRYPT)]);
        $blocked = $this->userModel->create(['phone'=>'+25472500004','email'=>'blocked@test.com','password_hash'=>password_hash('pass', PASSWORD_BCRYPT)]);

        $response = $this->post("/api/block", [
            'blocker_id'=>$blocker,
            'blocked_id'=>$blocked
        ]);

        $response->assertStatus(201);
        $block = $this->blockModel->latest();
        $this->assertEquals($blocked, $block['blocked_id']);
    }
}
