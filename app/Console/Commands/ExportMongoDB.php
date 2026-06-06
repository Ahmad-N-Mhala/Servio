<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class ExportMongoDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mongodb:export {--output= : Output directory}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export MongoDB database to JSON/BSON files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting MongoDB Export...');

        $config = Config::get('database.connections.mongodb');
        $dbName = $config['database'];
        $host = $config['host'];
        $port = $config['port'];

        // Default output dir in storage/backups
        $timestamp = date('Y-m-d_H-i-s');
        $outputDir = $this->option('output') ?: storage_path("backups/mongodb_{$timestamp}");

        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $this->info("Exporting database '{$dbName}' to: {$outputDir}");

        // Using mongodump (requires mongodump installed on system)
        // Command: mongodump --host="127.0.0.1" --port=27017 --db="servio_db" --out="./backup"

        $command = sprintf(
            'mongodump --host="%s" --port=%s --db="%s" --out="%s"',
            $host,
            $port,
            $dbName,
            $outputDir
        );

        $this->info("Running: {$command}");

        exec($command.' 2>&1', $output, $returnVar);

        if ($returnVar === 0) {
            $this->info('✅ MongoDB export successful!');
            $this->info("Backup location: {$outputDir}/{$dbName}");
        } else {
            // Fallback: If mongodump missing, try internal export (less robust but works for small data)
            $this->warn('⚠️ mongodump failed or not found. Trying JSON fallback export...');
            $this->exportJsonFallback($outputDir);
        }
    }

    private function exportJsonFallback($outputDir)
    {
        // Get all collections
        $collections = \DB::connection('mongodb')->listCollections();

        foreach ($collections as $collectionInfo) {
            $collectionName = $collectionInfo->getName();
            $this->info("Exporting collection: {$collectionName}...");

            $data = \DB::collection($collectionName)->get()->toArray();
            $json = json_encode($data, JSON_PRETTY_PRINT);

            file_put_contents("{$outputDir}/{$collectionName}.json", $json);
        }

        $this->info('✅ JSON fallback export successful!');
    }
}
