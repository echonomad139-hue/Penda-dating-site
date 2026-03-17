<?php
namespace App\Services;

use Twilio\Rest\Client;

class WhatsAppService
{
    private string $twilioSid;
    private string $twilioToken;
    private string $whatsappFrom;

    public function __construct()
    {
        $this->twilioSid = $_ENV['TWILIO_SID'];
        $this->twilioToken = $_ENV['TWILIO_TOKEN'];
        $this->whatsappFrom = $_ENV['TWILIO_WHATSAPP_FROM']; // e.g., "whatsapp:+14155238886"
    }

    public function sendOTP(string $toNumber, int $otp): bool
    {
        try {
            $client = new Client($this->twilioSid, $this->twilioToken);

            $to = "whatsapp:" . $toNumber;

            $message = "Your Pendo OTP is: {$otp}. It expires in 10 minutes.";

            $client->messages->create($to, [
                'from' => $this->whatsappFrom,
                'body' => $message
            ]);

            error_log("WhatsApp OTP sent to {$toNumber}: {$otp}");
            return true;

        } catch (\Exception $e) {
            error_log("WhatsApp OTP failed to {$toNumber}: " . $e->getMessage());
            return false;
        }
    }
}