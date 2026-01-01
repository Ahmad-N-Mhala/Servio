<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$timestamp = date('Ymd_His');
$backupDir = __DIR__ . "/backups/backup_$timestamp";

if (!file_exists($backupDir)) {
    mkdir($backupDir, 0755, true);
}

try {
    //$db = DB::connection('mongodb')->getManager()->createDataUri(); 
    // Just use client
    $connection = DB::connection('mongodb');
    // For jenssegers or laravel-mongodb
    if (method_exists($connection, 'getMongoDB')) {
        $mongoDb = $connection->getMongoDB();
    } elseif (method_exists($connection, 'getDatabase')) {
        $mongoDb = $connection->getDatabase();
    } else {
        // Fallback for some drivers: accessing client directly
        throw new Exception("Cannot access MongoDB instance from connection.");
    }

    $collections = $mongoDb->listCollections();

    echo "Starting backup to $backupDir...\n";

    foreach ($collections as $collectionInfo) {
        $collectionName = $collectionInfo->getName();
        $data = DB::connection('mongodb')->table($collectionName)->get();

        $filePath = "$backupDir/$collectionName.json";
        file_put_contents($filePath, $data->toJson(JSON_PRETTY_PRINT));
        echo "Saved $collectionName (" . count($data) . " records)\n";
    }

    echo "Backup completed successfully.\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
