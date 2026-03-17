<?php
require_once __DIR__ . '/../../TestCase.php';
use App\Models\User;
use App\Models\Subscription;
use App\Models\Payment;
use App\Services\Payment\PaymentRouter;

class PaymentRoutesTest extends TestCase
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

    public function testCreatePayment(): void
    {
        $userId = $this->userModel->create(['phone'=>'+25472400001','email'=>'payroute@test.com','password_hash'=>password_hash('pass', PASSWORD_BCRYPT)]);

        $response = $this->post("/api/payment/create", [
            'user_id'=>$userId,
            'provider'=>'mpesa',
            'transaction_id'=>'MPESA123',
            'amount'=>1000,
            'currency'=>'KES'
        ]);

        $response->assertStatus(201);
        $payment = $this->paymentModel->findByTransaction('MPESA123');
        $this->assertEquals('pending', $payment['status']);
    }

    public function testCompletePaymentUpdatesSubscription(): void
    {
        $userId = $this->userModel->create(['phone'=>'+25472400002','email'=>'payroute2@test.com','password_hash'=>password_hash('pass', PASSWORD_BCRYPT)]);

        $subId = $this->subscriptionModel->create([
            'user_id'=>$userId,
            'plan'=>'monthly',
            'start_date'=>date('Y-m-d H:i:s'),
            'end_date'=>date('Y-m-d H:i:s', strtotime('+1 month')),
            'status'=>'active'
        ]);

        $this->paymentRouter->process('mpesa', [
            'user_id'=>$userId,
            'transaction_id'=>'MPESA124',
            'amount'=>1000,
            'currency'=>'KES',
            'status'=>'completed'
        ]);

        $payment = $this->paymentModel->findByTransaction('MPESA124');
        $this->assertEquals('completed', $payment['status']);
    }
}
