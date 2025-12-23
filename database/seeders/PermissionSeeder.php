<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use Illuminate\Support\Facades\Config;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionGroups = Config::get('permissions');

        if (!$permissionGroups) {
            $this->command->info('No permissions found in config/permissions.php');
            return;
        }

        foreach ($permissionGroups as $groupKey => $groupData) {
            foreach ($groupData['permissions'] as $permissionName) {
                Permission::firstOrCreate(
                    ['name' => $permissionName, 'guard_name' => 'web'],
                    ['group' => $groupKey] // Optional: if we want to store group in DB later
                );
            }
        }

        $this->command->info('Permissions seeded successfully.');
    }
}
