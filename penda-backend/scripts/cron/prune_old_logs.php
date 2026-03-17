<?php

$dir = __DIR__ . '/../../storage/logs/';
$files = glob($dir . '*');

$pruned = 0;
foreach ($files as $file) {
    if (is_file($file) && filemtime($file) < time() - (30 * 24 * 3600)) {
        unlink($file);
        $pruned++;
    }
}

file_put_contents($dir . 'cron_prune.log', 
    "[".date('Y-m-d H:i:s')."] Pruned $pruned old logs\n", FILE_APPEND);

echo "✅ Pruned $pruned old logs.\n";
