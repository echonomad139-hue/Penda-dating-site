<?php
/**
 * Penda Backend Migration Runner
 * Migrates all database tables in the correct order
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Database\Migration;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

echo "⚡ Starting Penda Backend migrations...\n";

// List all migration files in the proper order
$migrations = [
    '2026_01_01_create_users_table.php',
    '2026_01_02_create_otp_verifications_table.php',
    '2026_01_03_create_profiles_table.php',
    '2026_01_04_create_photos_table.php',
    '2026_01_05_create_interactions_table.php',
    '2026_01_06_create_matches_table.php',
    '2026_01_07_create_messages_table.php',
    '2026_01_08_create_blocks_table.php',
    '2026_01_09_create_reports_table.php',
    '2026_01_10_create_subscriptions_table.php',
    '2026_01_11_create_payments_table.php',
    '2026_01_12_create_user_settings_table.php'
];

foreach ($migrations as $file) {
    $path = __DIR__ . '/../database/migrations/' . $file;
    if (file_exists($path)) {
        require_once $path;
        $className = pathinfo($file, PATHINFO_FILENAME);
        if (class_exists($className)) {
            $migration = new $className();
            $migration->up();
            echo "✅ Migrated: $className\n";
        }
    }
}

echo "🎯 All migrations completed successfully!\n";
