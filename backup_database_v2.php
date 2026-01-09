http://127.0.0.1:8000/en<?php

require 'vendor/autoload.php';

use MongoDB\Client;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$uri = env('DB_DSN', 'mongodb://127.0.0.1:27017');
$databaseName = env('DB_DATABASE', 'servio');

try {
    $client = new Client($uri);
    $database = $client->selectDatabase($databaseName);
    $collections = $database->listCollections();

    $backupDir = __DIR__ . '/database_backup_' . date('Y-m-d_H-i-s');
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
    }

    echo "Starting backup for database: $databaseName\n";
    echo "Backup directory: $backupDir\n";

    foreach ($collections as $collectionInfo) {
        $collectionName = $collectionInfo->getName();
        $collection = $database->selectCollection($collectionName);
        $documents = $collection->find();

        $data = [];
        foreach ($documents as $document) {
            // Convert BSON objects to JSON compatible array
            $data[] = json_decode(json_encode($document), true);
        }

        $json = json_encode($data, JSON_PRETTY_PRINT);
        file_put_contents("$backupDir/$collectionName.json", $json);
        echo "Backed up collection: $collectionName\n";
    }

    echo "Backup completed successfully.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

function env($key, $default = null)
{
    if (array_key_exists($key, $_ENV)) {
        return $_ENV[$key];
    }
    return $default;
}
