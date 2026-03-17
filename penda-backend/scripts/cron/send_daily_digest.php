<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\User;
use App\Models\Match;
use App\Models\Message;
use App\Models\Profile;
use App\Services\NotificationService;

$notifications = new NotificationService();

$premiumUsers = (new User())->getPremiumUsers();

foreach ($premiumUsers as $user) {
    $newMatches = (new Match())->getNewMatches($user['id']);
    $newMessages = (new Message())->getUnreadMessages($user['id']);
    $newProfiles = (new Profile())->getNewProfilesNearby($user['latitude'], $user['longitude']);

    $digest = [
        'matches' => count($newMatches),
        'messages' => count($newMessages),
        'new_profiles' => count($newProfiles)
    ];

    $notifications->sendDailyDigest($user['id'], $digest);
}

file_put_contents(__DIR__ . '/../../storage/logs/cron_digest.log',
    "[".date('Y-m-d H:i:s')."] Sent daily digest to ".count($premiumUsers)." users\n", FILE_APPEND);

echo "✅ Sent daily digest to " . count($premiumUsers) . " users.\n";
