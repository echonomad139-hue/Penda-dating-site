<?php
/**
 * Penda Backend Helper Functions
 * Utilities for DB access, file handling, notifications, and general services
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\NotificationService;
use App\Services\SmsService;
use App\Services\EmailService;
use App\Models\User;
use App\Models\Profile;
use App\Models\Subscription;
use App\Models\Photo;
use App\Models\Message;

/**
 * Generate a random OTP code
 *
 * @param int $length
 * @return string
 */
function generateOtp(int $length = 6): string
{
    return str_pad(random_int(0, pow(10, $length)-1), $length, '0', STR_PAD_LEFT);
}

/**
 * Send notification to user
 *
 * @param int $userId
 * @param string $message
 */
function sendUserNotification(int $userId, string $message): void
{
    $notifications = new NotificationService();
    $notifications->send($userId, $message);
}

/**
 * Send SMS via SmsService
 *
 * @param string $phone
 * @param string $message
 */
function sendSms(string $phone, string $message): void
{
    $sms = new SmsService();
    $sms->send($phone, $message);
}

/**
 * Send Email via EmailService
 *
 * @param string $email
 * @param string $subject
 * @param string $body
 */
function sendEmail(string $email, string $subject, string $body): void
{
    $mailer = new EmailService();
    $mailer->send($email, $subject, $body);
}

/**
 * Format user profile data (full integration with profiles and photos)
 *
 * @param int $userId
 * @return array|null
 */
function getFullProfile(int $userId): ?array
{
    $userModel = new User();
    $profileModel = new Profile();
    $photoModel = new Photo();

    $user = $userModel->find($userId);
    if (!$user) return null;

    $profile = $profileModel->find($userId);
    $photos = $photoModel->getByUserId($userId);

    return [
        'user' => $user,
        'profile' => $profile,
        'photos' => $photos
    ];
}

/**
 * Check if a user is premium
 *
 * @param int $userId
 * @return bool
 */
function isPremiumUser(int $userId): bool
{
    $subscriptionModel = new Subscription();
    $sub = $subscriptionModel->getActiveSubscription($userId);

    return $sub !== null && in_array($sub['status'], ['active']);
}

/**
 * Delete temporary uploaded files older than X seconds
 *
 * @param string $directory
 * @param int $seconds
 * @return int Deleted file count
 */
function cleanTempUploads(string $directory, int $seconds = 3600): int
{
    $files = glob($directory . '*');
    $deleted = 0;

    foreach ($files as $file) {
        if (filemtime($file) < time() - $seconds) {
            unlink($file);
            $deleted++;
        }
    }

    return $deleted;
}

/**
 * Log messages to a file in storage/logs/
 *
 * @param string $filename
 * @param string $message
 */
function logMessage(string $filename, string $message): void
{
    $dir = __DIR__ . '/../storage/logs/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    file_put_contents($dir . $filename, "[".date('Y-m-d H:i:s')."] $message\n", FILE_APPEND);
}

/**
 * Calculate age from date_of_birth
 *
 * @param string $dob
 * @return int
 */
function calculateAge(string $dob): int
{
    $birth = new DateTime($dob);
    $today = new DateTime();
    return $today->diff($birth)->y;
}

/**
 * Generate a random transaction ID for payments
 *
 * @param string $prefix
 * @return string
 */
function generateTransactionId(string $prefix = 'TX'): string
{
    return $prefix . strtoupper(bin2hex(random_bytes(5))) . time();
}

/**
 * Helper to get all new matches for a user (unread)
 *
 * @param int $userId
 * @return array
 */
function getNewMatches(int $userId): array
{
    $matchModel = new \App\Models\Match();
    $messageModel = new Message();

    $matches = $matchModel->getByUserId($userId);
    foreach ($matches as &$match) {
        $match['unread_messages'] = $messageModel->getUnreadCount($match['id'], $userId);
    }

    return $matches;
}

/**
 * Convert distance between two coordinates (latitude, longitude) in km
 *
 * @param float $lat1
 * @param float $lon1
 * @param float $lat2
 * @param float $lon2
 * @return float
 */
function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
{
    $earthRadius = 6371; // km

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) * sin($dLon/2);

    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $earthRadius * $c;
}
