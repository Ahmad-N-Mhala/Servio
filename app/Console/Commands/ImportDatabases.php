<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class ImportDatabases extends Command
{
    protected $signature = 'db:import {--input= : Input directory} {--force : Force import even if databases exist}';
    protected $description = 'Import all databases from SQL dumps';

    public function handle()
    {
        $inputDir = $this->option('input') ?: database_path('dumps');
        
        if (!is_dir($inputDir)) {
            $this->error("Directory not found: {$inputDir}");
            return 1;
        }

        $this->info('Importing databases...');
        $this->newLine();

        // Import central database
        $this->importCentralDatabase($inputDir);

        // Import tenant databases
        $this->importTenantDatabases($inputDir);

        $this->newLine();
        $this->info('✅ All databases imported successfully!');
        
        return 0;
    }

    protected function importCentralDatabase($inputDir)
    {
        $defaultConnection = Config::get('database.default');
        $config = Config::get("database.connections.{$defaultConnection}");
        $driver = $config['driver'];
        
        $this->info("Importing central database ({$driver})...");
        
        $dbName = $config['database'];
        $host = $config['host'];
        $port = $config['port'] ?? ($driver === 'pgsql' ? '5432' : '3306');
        $username = $config['username'];
        $password = $config['password'];
        
        // Find central database dump
        $dumpFile = null;
        $files = glob("{$inputDir}/central_*.sql");
        
        if (empty($files)) {
            $this->warn("  ⚠️  No central database dump found in {$inputDir}");
            return;
        }
        
        $dumpFile = $files[0];
        $this->info("  📄 Found dump: " . basename($dumpFile));
        
        // Check if database exists
        if ($driver === 'pgsql') {
            $checkCommand = sprintf(
                'PGPASSWORD=%s psql -h %s -p %s -U %s -lqt 2>/dev/null | cut -d \| -f 1 | grep -qw %s',
                escapeshellarg($password),
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($dbName)
            );
        } else {
            $checkCommand = sprintf(
                'mysql -h %s -P %s -u %s -p%s -e "SHOW DATABASES LIKE \'%s\'" 2>/dev/null | grep -q %s',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($dbName),
                escapeshellarg($dbName)
            );
        }
        
        exec($checkCommand, $output, $returnVar);
        $dbExists = $returnVar === 0;
        
        if ($dbExists && !$this->option('force')) {
            if (!$this->confirm("  Database '{$dbName}' already exists. Overwrite? (this will delete all data)", false)) {
                $this->warn("  ⏭️  Skipped central database import");
                return;
            }
        }
        
        // Drop and recreate database if it exists
        if ($dbExists) {
            $this->info("  🗑️  Dropping existing database...");
            if ($driver === 'pgsql') {
                $dropCommand = sprintf(
                    'PGPASSWORD=%s psql -h %s -p %s -U %s -c "DROP DATABASE IF EXISTS %s" 2>/dev/null',
                    escapeshellarg($password),
                    escapeshellarg($host),
                    escapeshellarg($port),
                    escapeshellarg($username),
                    escapeshellarg($dbName)
                );
            } else {
                $dropCommand = sprintf(
                    'mysql -h %s -P %s -u %s -p%s -e "DROP DATABASE IF EXISTS %s" 2>/dev/null',
                    escapeshellarg($host),
                    escapeshellarg($port),
                    escapeshellarg($username),
                    escapeshellarg($password),
                    escapeshellarg($dbName)
                );
            }
            exec($dropCommand);
        }
        
        // Create database
        $this->info("  🆕 Creating database...");
        if ($driver === 'pgsql') {
            $createCommand = sprintf(
                'PGPASSWORD=%s psql -h %s -p %s -U %s -c "CREATE DATABASE %s" 2>/dev/null',
                escapeshellarg($password),
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($dbName)
            );
        } else {
            $createCommand = sprintf(
                'mysql -h %s -P %s -u %s -p%s -e "CREATE DATABASE %s CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci" 2>/dev/null',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($dbName)
            );
        }
        exec($createCommand, $output, $returnVar);
        
        if ($returnVar !== 0) {
            $this->error("  ❌ Failed to create database");
            return;
        }
        
        // Import dump
        $this->info("  📥 Importing data...");
        if ($driver === 'pgsql') {
            $importCommand = sprintf(
                'PGPASSWORD=%s psql -h %s -p %s -U %s -d %s < %s 2>/dev/null',
                escapeshellarg($password),
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($dbName),
                escapeshellarg($dumpFile)
            );
        } else {
            $importCommand = sprintf(
                'mysql -h %s -P %s -u %s -p%s %s < %s 2>/dev/null',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($dbName),
                escapeshellarg($dumpFile)
            );
        }
        
        exec($importCommand, $output, $returnVar);
        
        if ($returnVar === 0) {
            $this->info("  ✅ Central database imported successfully!");
        } else {
            $this->error("  ❌ Failed to import central database");
        }
    }

    protected function importTenantDatabases($inputDir)
    {
        $this->info('Importing tenant databases (MySQL)...');
        
        $config = Config::get('database.connections.mysql');
        $host = $config['host'];
        $port = $config['port'];
        $username = $config['username'];
        $password = $config['password'];
        
        // Find all tenant dumps
        $dumpFiles = glob("{$inputDir}/tenant_*.sql");
        
        if (empty($dumpFiles)) {
            $this->warn("  ⚠️  No tenant database dumps found in {$inputDir}");
            return;
        }
        
        foreach ($dumpFiles as $dumpFile) {
            // Extract database name from filename (tenant_<dbname>.sql)
            $filename = basename($dumpFile);
            if (preg_match('/^tenant_(.+)\.sql$/', $filename, $matches)) {
                $dbName = $matches[1];
                
                $this->info("  📄 Processing: {$filename} (database: {$dbName})");
                
                // Check if database exists
                $checkCommand = sprintf(
                    'mysql -h %s -P %s -u %s -p%s -e "SHOW DATABASES LIKE \'%s\'" 2>/dev/null | grep -q %s',
                    escapeshellarg($host),
                    escapeshellarg($port),
                    escapeshellarg($username),
                    escapeshellarg($password),
                    escapeshellarg($dbName),
                    escapeshellarg($dbName)
                );
                
                exec($checkCommand, $output, $returnVar);
                $dbExists = $returnVar === 0;
                
                if ($dbExists && !$this->option('force')) {
                    if (!$this->confirm("    Database '{$dbName}' already exists. Overwrite?", false)) {
                        $this->warn("    ⏭️  Skipped");
                        continue;
                    }
                }
                
                // Drop and recreate database if it exists
                if ($dbExists) {
                    $this->info("    🗑️  Dropping existing database...");
                    $dropCommand = sprintf(
                        'mysql -h %s -P %s -u %s -p%s -e "DROP DATABASE IF EXISTS %s" 2>/dev/null',
                        escapeshellarg($host),
                        escapeshellarg($port),
                        escapeshellarg($username),
                        escapeshellarg($password),
                        escapeshellarg($dbName)
                    );
                    exec($dropCommand);
                }
                
                // Create database
                $this->info("    🆕 Creating database...");
                $createCommand = sprintf(
                    'mysql -h %s -P %s -u %s -p%s -e "CREATE DATABASE %s CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci" 2>/dev/null',
                    escapeshellarg($host),
                    escapeshellarg($port),
                    escapeshellarg($username),
                    escapeshellarg($password),
                    escapeshellarg($dbName)
                );
                exec($createCommand, $output, $returnVar);
                
                if ($returnVar !== 0) {
                    $this->error("    ❌ Failed to create database");
                    continue;
                }
                
                // Import dump
                $this->info("    📥 Importing data...");
                $importCommand = sprintf(
                    'mysql -h %s -P %s -u %s -p%s %s < %s 2>/dev/null',
                    escapeshellarg($host),
                    escapeshellarg($port),
                    escapeshellarg($username),
                    escapeshellarg($password),
                    escapeshellarg($dbName),
                    escapeshellarg($dumpFile)
                );
                
                exec($importCommand, $output, $returnVar);
                
                if ($returnVar === 0) {
                    $this->info("    ✅ Imported successfully!");
                } else {
                    $this->error("    ❌ Failed to import");
                }
            }
        }
    }
}

