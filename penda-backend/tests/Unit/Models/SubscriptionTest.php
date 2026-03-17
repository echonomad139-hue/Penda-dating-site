<?php
require_once __DIR__ . '/../../TestCase.php';
use App\Models\User;
use App\Models\Subscription;

class SubscriptionTest extends TestCase
{
    public function testCreateAndRetrieveSubscription(): void
    {
        $userModel = new User();
        $subscriptionModel = new Subscription();

        $userId = $userModel->create([
            'phone' => '+25470000200',
            'email' => 'subuser@test.com',
            'password_hash' => password_hash('secret123', PASSWORD_BCRYPT)
        ]);

        $start = date('Y-m-d H:i:s');
        $end = date('Y-m-d H:i:s', strtotime('+1 month'));

        $subId = $subscriptionModel->create([
            'user_id' => $userId,
            'plan' => 'monthly',
            'start_date' => $start,
            'end_date' => $end,
            'status' => 'active'
        ]);

        $this->assertIsInt($subId);

        $sub = $subscriptionModel->find($subId);
        $this->assertEquals('monthly', $sub['plan']);
        $this->assertEquals('active', $sub['status']);
    }

    public function testExpireSubscription(): void
    {
        $userModel = new User();
        $subscriptionModel = new Subscription();

        $userId = $userModel->create([
            'phone' => '+25470000201',
            'email' => 'expireuser@test.com',
            'password_hash' => password_hash('secret123', PASSWORD_BCRYPT)
        ]);

        $subId = $subscriptionModel->create([
            'user_id' => $userId,
            'plan' => 'weekly',
            'start_date' => date('Y-m-d H:i:s', strtotime('-2 weeks')),
            'end_date' => date('Y-m-d H:i:s', strtotime('-1 week')),
            'status' => 'active'
        ]);

        $subscriptionModel->expire($subId);

        $sub = $subscriptionModel->find($subId);
        $this->assertEquals('expired', $sub['status']);
    }
}
