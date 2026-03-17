<?php
require_once __DIR__ . '/../../TestCase.php';
use App\Models\User;
use App\Models\Report;

class ReportTest extends TestCase
{
    public function testCreateAndRetrieveReport(): void
    {
        $userModel = new User();
        $reportModel = new Report();

        $reporter = $userModel->create([
            'phone' => '+25470000210',
            'email' => 'reporter@test.com',
            'password_hash' => password_hash('secret123', PASSWORD_BCRYPT)
        ]);

        $reported = $userModel->create([
            'phone' => '+25470000211',
            'email' => 'reported@test.com',
            'password_hash' => password_hash('secret123', PASSWORD_BCRYPT)
        ]);

        $reportId = $reportModel->create([
            'reporter_id' => $reporter,
            'reported_id' => $reported,
            'reason' => 'Inappropriate content',
            'description' => 'Sent offensive messages'
        ]);

        $this->assertIsInt($reportId);

        $report = $reportModel->find($reportId);
        $this->assertEquals('pending', $report['status']);
        $this->assertEquals('Inappropriate content', $report['reason']);
    }

    public function testUpdateReportStatus(): void
    {
        $userModel = new User();
        $reportModel = new Report();

        $reporter = $userModel->create([
            'phone' => '+25470000212',
            'email' => 'reporter2@test.com',
            'password_hash' => password_hash('secret123', PASSWORD_BCRYPT)
        ]);

        $reported = $userModel->create([
            'phone' => '+25470000213',
            'email' => 'reported2@test.com',
            'password_hash' => password_hash('secret123', PASSWORD_BCRYPT)
        ]);

        $reportId = $reportModel->create([
            'reporter_id' => $reporter,
            'reported_id' => $reported,
            'reason' => 'Spam',
            'description' => 'Repeated spam messages'
        ]);

        $reportModel->updateStatus($reportId, 'reviewed');

        $report = $reportModel->find($reportId);
        $this->assertEquals('reviewed', $report['status']);
    }
}
