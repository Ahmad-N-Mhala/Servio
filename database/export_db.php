<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

$backupPath = __DIR__ . '/backup_data';

if (!File::exists($backupPath)) {
    File::makeDirectory($backupPath, 0755, true);
}

// Get all collections
// Note: MongoDB specific command to list collections
$collections = DB::connection('mongodb')->getMongoDB()->listCollections();

echo "Backing up database to $backupPath...\n";

foreach ($collections as $collectionInfo) {
    $collectionName = $collectionInfo['name'];

    // Skip system collections
    if (str_starts_with($collectionName, 'system.')) {
        continue;
    }

    echo "Exporting $collectionName...\n";

    $data = DB::connection('mongodb')->table($collectionName)->get();

    // Pretty print JSON
    $json = $data->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    File::put("$backupPath/$collectionName.json", $json);
}

echo "Backup completed successfully.\n";
