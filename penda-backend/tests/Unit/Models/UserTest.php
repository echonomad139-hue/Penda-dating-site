<?php
require_once __DIR__ . '/../../TestCase.php';
use App\Models\User;
use App\Models\Profile;
use App\Models\Subscription;
use App\Models\Payment;

class UserTest extends TestCase
{
    public function testCreateUserWithFullData(): void
    {
        $userModel = new User();

        $userId = $userModel->create([
            'phone' => '+254700000010',
            'email' => 'fulluser@test.com',
            'password_hash' => password_hash('Secret123!', PASSWORD_BCRYPT),
            'is_verified' => true,
            'is_active' => true,
            'role' => 'user'
        ]);

        $this->assertIsInt($userId);

        // Fetch user and validate columns
        $user = $userModel->find($userId);
        $this->assertEquals('+254700000010', $user['phone']);
        $this->assertTrue((bool)$user['is_verified']);
        $this->assertEquals('user', $user['role']);
    }

    public function testUserHasProfileAndSubscription(): void
    {
        $userModel = new User();
        $profileModel = new Profile();
        $subscriptionModel = new Subscription();

        $userId = $userModel->create([
            'phone' => '+254700000011',
            'email' => 'relation@test.com',
            'password_hash' => password_hash('Secret123!', PASSWORD_BCRYPT)
        ]);

        // Create profile
        $profileId = $profileModel->create([
            'user_id' => $userId,
            'display_name' => 'Tester',
            'date_of_birth' => '1995-05-05',
            'gender' => 'male',
            'bio' => 'I am a test user',
            'city' => 'Nyeri'
        ]);
        $this->assertEquals($userId, $profileId);

        // Create subscription
        $subId = $subscriptionModel->create([
            'user_id' => $userId,
            'plan' => 'monthly',
            'start_date' => date('Y-m-d H:i:s'),
            'end_date' => date('Y-m-d H:i:s', strtotime('+1 month')),
            'status' => 'active'
        ]);
        $this->assertIsInt($subId);
    }

    public function testUserPayments(): void
    {
        $userModel = new User();
        $paymentModel = new Payment();

        $userId = $userModel->create([
            'phone' => '+254700000012',
            'email' => 'payment@test.com',
            'password_hash' => password_hash('Secret123!', PASSWORD_BCRYPT)
        ]);

        $paymentId = $paymentModel->create([
            'user_id' => $userId,
            'provider' => 'mpesa',
            'transaction_id' => 'TX' . time(),
            'amount' => 1500.00,
            'currency' => 'KES',
            'status' => 'completed'
        ]);

        $this->assertIsInt($paymentId);

        $payment = $paymentModel->find($paymentId);
        $this->assertEquals('mpesa', $payment['provider']);
        $this->assertEquals(1500.00, $payment['amount']);
    }
}
