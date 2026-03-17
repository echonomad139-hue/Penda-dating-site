<?php
/**
 * Penda Backend Seeder Runner
 * Seeds all tables with initial data: admin user, test users, subscriptions
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Database\Seeder;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

echo "⚡ Starting Penda Backend seeding...\n";

// Seeder files
$seeders = [
    'AdminSeeder.php',           // Creates an admin user
    'TestUsersSeeder.php',       // Creates multiple test users with profiles
    'PhotosSeeder.php',          // Adds photos for test users
    'InteractionsSeeder.php',    // Creates likes, passes, superlikes
    'MatchesSeeder.php',         // Creates test matches
    'MessagesSeeder.php',        // Adds conversation messages
    'BlocksSeeder.php',          // Adds blocked user entries
    'ReportsSeeder.php',         // Adds sample reports
    'SubscriptionsSeeder.php',   // Seeds subscription plans
    'PaymentsSeeder.php',        // Seeds payment transactions
    'UserSettingsSeeder.php'     // Seeds user settings
];

foreach ($seeders as $file) {
    $path = __DIR__ . '/../database/seeds/' . $file;
    if (file_exists($path)) {
        require_once $path;
        $className = pathinfo($file, PATHINFO_FILENAME);
        if (class_exists($className)) {
            $seeder = new $className();
            $seeder->run();
            echo "✅ Seeded: $className\n";
        }
    }
}

echo "🎯 All seeders executed successfully!\n";
