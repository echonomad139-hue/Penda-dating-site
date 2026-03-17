<?php
require_once __DIR__ . '/../../TestCase.php';
use App\Models\User;
use App\Models\Subscription;
use App\Models\Payment;
use App\Services\Payment\MpesaService;
use App\Services\Payment\PaymentRouter;

class PaymentServiceTest extends TestCase
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

    public function testMpesaPaymentCompletesSubscription(): void
    {
        $userId = $this->userModel->create(['phone'=>'+25470200000','email'=>'payuser@test.com','password_hash'=>password_hash('pass123',PASSWORD_BCRYPT)]);

        // Simulate subscription
        $subId = $this->subscriptionModel->create([
            'user_id'=>$userId,
            'plan'=>'monthly',
            'start_date'=>date('Y-m-d H:i:s'),
            'end_date'=>date('Y-m-d H:i:s', strtotime('+1 month')),
            'status'=>'active'
        ]);

        $paymentId = $this->paymentRouter->process('mpesa', [
            'user_id'=>$userId,
            'transaction_id'=>'MP123456',
            'amount'=>1000.00,
            'currency'=>'KES',
            'status'=>'completed'
        ]);

        $this->assertIsInt($paymentId);

        $payment = $this->paymentModel->find($paymentId);
        $this->assertEquals('completed', $payment['status']);
    }

    public function testDuplicateTransactionFails(): void
    {
        $userId = $this->userModel->create(['phone'=>'+25470200001','email'=>'dupuser@test.com','password_hash'=>password_hash('pass123',PASSWORD_BCRYPT)]);

        $this->paymentRouter->process('paypal', [
            'user_id'=>$userId,
            'transaction_id'=>'TXN123',
            'amount'=>500.00,
            'currency'=>'KES',
            'status'=>'completed'
        ]);

        $this->expectException(PDOException::class);
        $this->paymentRouter->process('paypal', [
            'user_id'=>$userId,
            'transaction_id'=>'TXN123', // duplicate
            'amount'=>500.00,
            'currency'=>'KES',
            'status'=>'completed'
        ]);
    }
}
