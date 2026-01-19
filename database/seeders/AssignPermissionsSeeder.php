<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class AssignPermissionsSeeder extends Seeder
{
    public function run()
    {
        // 1. Assign ALL permissions to Owner
        $ownerRole = Role::where('name', 'owner')->first();
        if ($ownerRole) {
            $allPermissions = Permission::all();
            $ownerRole->syncPermissions($allPermissions);
            $this->command->info("Assigned " . $allPermissions->count() . " permissions to Owner.");
        } else {
            $this->command->error("Owner role not found.");
        }

        // 2. Assign Kitchen Permissions to Chef & Kitchen Staff
        $kitchenPermissions = Permission::where('name', 'like', '%kitchen%')
            ->orWhere('name', 'like', '%order%')
            ->get();

        $chefRole = Role::where('name', 'head_chef')->first();
        if ($chefRole)
            $chefRole->syncPermissions($kitchenPermissions);

        $staffRole = Role::where('name', 'kitchen_staff')->first();
        if ($staffRole)
            $staffRole->syncPermissions($kitchenPermissions);

        // 3. Assign Waiter Permissions
        $waiterPermissions = Permission::where('name', 'like', '%order%')
            ->orWhere('name', 'like', '%table%')
            ->get();

        $waiterRole = Role::where('name', 'waiter')->first();
        if ($waiterRole)
            $waiterRole->syncPermissions($waiterPermissions);

        $this->command->info('Permissions assigned to roles.');
    }
}
