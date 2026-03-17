<?php

namespace App\Services\Payment;

use PDO;

class PaymentRouter
{
    protected PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function route(string $provider)
    {
        return match($provider) {
            'mpesa' => new MpesaService($this->db),
            'airtel' => new AirtelService($this->db),
            'paypal' => new PaypalService($this->db),
            default => throw new \Exception("Unsupported payment provider")
        };
    }
}
