<?php
require_once __DIR__ . '/../../TestCase.php';
use App\Models\User;
use App\Services\NotificationService;

class NotificationServiceTest extends TestCase
{
    protected NotificationService $notificationService;
    protected User $userModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->notificationService = new NotificationService();
        $this->userModel = new User();
    }

    public function testSendPushNotification(): void
    {
        $userId = $this->userModel->create(['phone'=>'+25470300000','email'=>'notifuser@test.com','password_hash'=>password_hash('pass123',PASSWORD_BCRYPT)]);

        $result = $this->notificationService->sendPush($userId, 'New Match!', 'You have a new match waiting');
        $this->assertTrue($result);
    }

    public function testSendEmailNotification(): void
    {
        $userId = $this->userModel->create(['phone'=>'+25470300001','email'=>'emailnotif@test.com','password_hash'=>password_hash('pass123',PASSWORD_BCRYPT)]);

        $result = $this->notificationService->sendEmail($userId, 'Welcome to Penda', 'Thanks for joining!');
        $this->assertTrue($result);
    }
}
