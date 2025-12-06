<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class ExportDatabases extends Command
{
    protected $signature = 'db:export {--output= : Output directory}';
    protected $description = 'Export all databases (central + tenants) to SQL dumps';

    public function handle()
    {
        $outputDir = $this->option('output') ?: database_path('dumps');
        
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $this->info('Exporting databases...');
        $this->newLine();

        // Export central database (PostgreSQL)
        $this->exportCentralDatabase($outputDir);

        // Export tenant databases (MySQL)
        $this->exportTenantDatabases($outputDir);

        $this->newLine();
        $this->info('✅ All databases exported successfully!');
        $this->info("📁 Dumps saved to: {$outputDir}");
    }

    protected function exportCentralDatabase($outputDir)
    {
        $defaultConnection = Config::get('database.default');
        $config = Config::get("database.connections.{$defaultConnection}");
        $driver = $config['driver'];
        
        $this->info("Exporting central database ({$driver})...");
        
        $dbName = $config['database'];
        $host = $config['host'];
        $port = $config['port'] ?? ($driver === 'pgsql' ? '5432' : '3306');
        $username = $config['username'];
        $password = $config['password'];
        
        $outputFile = "{$outputDir}/central_{$dbName}.sql";
        
        if ($driver === 'pgsql') {
            $command = sprintf(
                'PGPASSWORD=%s pg_dump -h %s -p %s -U %s -d %s --no-owner --no-acl > %s 2>&1',
                escapeshellarg($password),
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($dbName),
                escapeshellarg($outputFile)
            );
        } else {
            // MySQL
            $command = sprintf(
                'mysqldump -h %s -P %s -u %s -p%s %s --single-transaction --quick --lock-tables=false > %s 2>&1',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($dbName),
                escapeshellarg($outputFile)
            );
        }
        
        exec($command, $output, $returnVar);
        
        if ($returnVar === 0 && file_exists($outputFile)) {
            $size = $this->formatBytes(filesize($outputFile));
            $this->info("  ✅ Central DB exported: {$outputFile} ({$size})");
        } else {
            $this->error("  ❌ Failed to export central database");
            if (!empty($output)) {
                $this->error("  Error: " . implode("\n", array_slice($output, -3)));
            }
        }
    }

    protected function exportTenantDatabases($outputDir)
    {
        $this->info('Exporting tenant databases (MySQL)...');
        
        $tenants = Tenant::all();
        
        if ($tenants->isEmpty()) {
            $this->warn('  No tenants found');
            return;
        }

        $config = Config::get('database.connections.mysql');
        $host = $config['host'];
        $port = $config['port'];
        $username = $config['username'];
        $password = $config['password'];
        
        foreach ($tenants as $tenant) {
            $dbName = $tenant->id;
            $outputFile = "{$outputDir}/tenant_{$dbName}.sql";
            
            $command = sprintf(
                'mysqldump -h %s -P %s -u %s -p%s %s --single-transaction --quick --lock-tables=false > %s 2>&1',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($dbName),
                escapeshellarg($outputFile)
            );
            
            exec($command, $output, $returnVar);
            
            if ($returnVar === 0 && file_exists($outputFile)) {
                $size = $this->formatBytes(filesize($outputFile));
                $this->info("  ✅ Tenant '{$tenant->identifier}' ({$dbName}): {$outputFile} ({$size})");
            } else {
                $this->warn("  ⚠️  Failed to export tenant '{$tenant->identifier}' ({$dbName})");
            }
        }
    }

    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

