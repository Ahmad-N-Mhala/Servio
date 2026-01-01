<?php

namespace App\Console\Commands;

use App\Models\Permission;
use Illuminate\Console\Command;

class SyncPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync permissions from config file to database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Syncing permissions from config to database...');

        // Get all permissions from config
        $configPermissions = [];
        foreach (config('permissions') as $module => $data) {
            foreach ($data['permissions'] as $permission) {
                $configPermissions[] = $permission;
            }
        }

        $this->info('Found ' . count($configPermissions) . ' permissions in config');

        // Get existing permissions from database
        $existingPermissions = Permission::pluck('name')->toArray();
        $this->info('Found ' . count($existingPermissions) . ' permissions in database');

        // Find missing permissions
        $missingPermissions = array_diff($configPermissions, $existingPermissions);

        if (empty($missingPermissions)) {
            $this->info('✅ All permissions are already in sync!');
            return 0;
        }

        $this->warn('Found ' . count($missingPermissions) . ' missing permissions:');
        foreach ($missingPermissions as $permission) {
            $this->line('  - ' . $permission);
        }

        // Create missing permissions
        $this->info('Creating missing permissions...');
        $created = 0;

        foreach ($missingPermissions as $permission) {
            try {
                Permission::create([
                    'name' => $permission,
                    'guard_name' => 'web',
                ]);
                $this->info('  ✓ Created: ' . $permission);
                $created++;
            } catch (\Exception $e) {
                $this->error('  ✗ Failed to create: ' . $permission . ' - ' . $e->getMessage());
            }
        }

        // Clear permission cache
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $this->info('Permission cache cleared');

        $this->info('');
        $this->info('✅ Sync complete! Created ' . $created . ' new permissions.');

        return 0;
    }
}
