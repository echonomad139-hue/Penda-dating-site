<?php

require_once __DIR__ . '/../../vendor/autoload.php';

$dir = __DIR__ . '/../../storage/uploads/temp/';
$files = glob($dir . '*');

$deleted = 0;
foreach ($files as $file) {
    if (filemtime($file) < time() - 3600) { // older than 1 hour
        unlink($file);
        $deleted++;
    }
}

file_put_contents(__DIR__ . '/../../storage/logs/cron_cleanup.log', 
    "[".date('Y-m-d H:i:s')."] Deleted $deleted temp uploads\n", FILE_APPEND);

echo "✅ Deleted $deleted temp uploads.\n";
