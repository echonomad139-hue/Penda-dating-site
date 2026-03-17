<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    public function sendOTP(string $email, string $otp): bool
    {
        $mail = new PHPMailer(true);

        try {

            // SMTP SERVER SETTINGS
            $mail->isSMTP();
            
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'malikjuju321@gmail.com';   // your gmail
            $mail->Password   = 'wlzbaqvondhpbayh';         // gmail app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // EMAIL HEADERS
            $mail->setFrom('malikjuju321@gmail.com', 'Penda');
            $mail->addAddress($email);

            // EMAIL CONTENT
            $mail->isHTML(true);
            $mail->Subject = 'Penda Password Reset OTP';

            $mail->Body = "
                <div style='font-family:Arial,sans-serif'>
                    <h2>Penda OTP Verification</h2>
                    <p>You requested to reset your password.</p>
                    <p>Your OTP code is:</p>
                    <h1 style='letter-spacing:4px;color:#2563eb;'>{$otp}</h1>
                    <p>This code will expire in <b>10 minutes</b>.</p>
                    <p>If you did not request this, please ignore this email.</p>
                </div>
            ";

            $mail->AltBody = "Your Penda OTP code is: {$otp}";

            // SEND EMAIL
            $mail->send();

            return true;

        } catch (Exception $e) {

            error_log("Mailer Error: " . $mail->errorInfo);
            error_log("Exception: " . $e->getMessage());

            return false;
        }
    }
}