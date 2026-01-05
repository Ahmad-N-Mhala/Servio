<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$backupDir = __DIR__ . "/database/backup_data";

if (!file_exists($backupDir)) {
    mkdir($backupDir, 0755, true);
}

try {
    $connection = DB::connection('mongodb');

    // Attempt to access the database object directly compatible with MongoDB methods
    if (method_exists($connection, 'getMongoDB')) {
        $mongoDb = $connection->getMongoDB();
    } elseif (method_exists($connection, 'getDatabase')) { // common in newer drivers (laravel-mongodb)
        $mongoDb = $connection->getDatabase();
    } else {
        // Fallback or error if neither method exists
        // Depending on driver version, might need $connection->getMongoClient()->selectDatabase(...)
        // But for now, let's assume one of the above works or we list collections via manager if needed.
        throw new Exception("Cannot access MongoDB database object.");
    }

    $collections = $mongoDb->listCollections();

    echo "Updating git-tracked backup in $backupDir...\n";

    foreach ($collections as $collectionInfo) {
        $collectionName = $collectionInfo->getName();
        $data = DB::connection('mongodb')->table($collectionName)->get();

        // Convert ObjectIds and ISODates to string/standard format if needed, 
        // but default toJson usually handles it well enough for basic backup/restore.

        $filePath = "$backupDir/$collectionName.json";

        // Use JSON_PRETTY_PRINT for better diffs in git
        file_put_contents($filePath, $data->toJson(JSON_PRETTY_PRINT));
        echo "Updated $collectionName (" . count($data) . " records)\n";
    }

    echo "Backup update completed successfully.\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
