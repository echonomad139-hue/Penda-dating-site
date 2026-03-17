<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\Subscription;
use App\Services\NotificationService;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

$subscriptions = new Subscription();
$notifications = new NotificationService();

$expiringSubs = $subscriptions->getExpiringSubscriptions(3); // expires in 3 days

foreach ($expiringSubs as $sub) {
    $notifications->sendExpiryAlert($sub['user_id'], $sub['plan'], $sub['end_date']);
}

file_put_contents(__DIR__ . '/../../storage/logs/cron_notifications.log',
    "[".date('Y-m-d H:i:s')."] Notified ".count($expiringSubs)." users of expiring subscriptions\n", FILE_APPEND);

echo "✅ Notified " . count($expiringSubs) . " users.\n";
