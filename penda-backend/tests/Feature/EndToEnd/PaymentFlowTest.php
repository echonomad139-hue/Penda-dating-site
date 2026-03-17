<?php
require_once __DIR__ . '/../../TestCase.php';

use App\Models\User;
use App\Models\Subscription;
use App\Models\Payment;
use App\Services\Payment\PaymentRouter;

class PaymentFlowTest extends TestCase
{
    protected User $userModel;
    protected Subscription $subscriptionModel;
    protected Payment $paymentModel;
    protected PaymentRouter $paymentRouter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userModel = new User();
        $this->subscriptionModel = new Subscription();
        $this->paymentModel = new Payment();
        $this->paymentRouter = new PaymentRouter();
    }

    public function testFullPaymentFlow(): void
    {
        // 1️⃣ User creation
        $userId = $this->userModel->create([
            'phone'=>'+25473100001',
            'email'=>'payflow@test.com',
            'password_hash'=>password_hash('secure123', PASSWORD_BCRYPT)
        ]);

        // 2️⃣ Create subscription
        $subId = $this->subscriptionModel->create([
            'user_id'=>$userId,
            'plan'=>'monthly',
            'start_date'=>date('Y-m-d H:i:s'),
            'end_date'=>date('Y-m-d H:i:s', strtotime('+1 month')),
            'status'=>'active'
        ]);
        $subscription = $this->subscriptionModel->find($subId);
        $this->assertEquals('monthly', $subscription['plan']);

        // 3️⃣ Make payment via PaymentRouter
        $this->paymentRouter->process('mpesa', [
            'user_id'=>$userId,
            'transaction_id'=>'MPESA999',
            'amount'=>1000,
            'currency'=>'KES',
            'status'=>'completed'
        ]);

        $payment = $this->paymentModel->findByTransaction('MPESA999');
        $this->assertEquals('completed', $payment['status']);

        // 4️⃣ Ensure subscription remains active
        $subscription = $this->subscriptionModel->find($subId);
        $this->assertEquals('active', $subscription['status']);
    }
}
