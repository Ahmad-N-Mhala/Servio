<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class AssignPermissionsSeeder extends Seeder
{
    public function run()
    {
        // 1. Ensure all roles exist
        $rolesConfig = config('roles.roles');
        foreach ($rolesConfig as $key => $details) {
            Role::firstOrCreate(['name' => $key, 'guard_name' => 'web']);
        }

        // 2. Define Comprehensive Permission Sets
        $allPermissions = Permission::pluck('name')->toArray();

        $rolePermissions = [
            'owner' => $allPermissions, // Full Access

            'manager' => $this->getManagerPermissions($allPermissions),

            'head_chef' => [
                'view_dashboard',
                'view_kitchen',
                'update_item_status', // Kitchen
                'view_menu',
                'create_category',
                'edit_category',
                'delete_category',
                'create_item',
                'edit_item',
                'delete_item', // Menu
                'view_inventory',
                'view_inventory_reminders',
                'add_stock',
                'deduct_stock',
                'delete_inventory', // Inventory
                'view_waste',
                'record_waste',
                'delete_waste', // Waste
                'view_orders', // View incoming orders
            ],

            'kitchen_staff' => [
                'view_dashboard',
                'view_kitchen',
                'update_item_status',
                'view_orders',
                'view_menu', // Read-only menu
            ],

            'waiter' => [
                'view_dashboard',
                'view_pos',
                'create_order', // Basic POS access for ordering
                'view_orders',
                'create_order',
                'edit_order',
                'print_bill',
                'view_tables', // Table management
                'view_customers',
                'create_customer', // CRM for walk-ins
                'deliver_orders', // Serve food
                'view_menu',
                'view_order_status_screen', // Check if food is ready
            ],

            'cashier' => [
                'view_dashboard',
                'view_pos',
                'pos_system',
                'create_order',
                'discount_order',
                'void_order',
                'view_cash_register_history', // Full POS
                'view_orders',
                'create_order',
                'edit_order',
                'cancel_order',
                'print_bill',
                'view_customers',
                'create_customer',
                'edit_customer',
                'view_tables',
                'view_menu',
            ],

            'delivery_driver' => [
                'view_dashboard',
                'view_orders',
                'deliver_orders', // Manage own deliveries
            ],
        ];

        // 3. Assign Permissions
        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                // Filter valid permissions only
                $validPermissions = Permission::whereIn('name', $permissions)->pluck('name')->toArray();

                // If 'manager' or 'owner', they might have gathered permissions that don't exist in DB yet if not seeded, 
                // but we assume PermissionSeeder runs before this.

                // Sync
                $role->syncPermissions($validPermissions);
                $this->command->info("Assigned " . count($validPermissions) . " permissions to role: {$roleName}");
            } else {
                $this->command->error("Role not found: {$roleName}");
            }
        }
    }

    private function getManagerPermissions($all)
    {
        // filter out specific high-level owner-only perms if strictly needed
        // For now, Managers get almost everything except maybe 'delete_restaurant' (which likely doesn't exist as a perm)
        // Let's exclude Billing for Manager?
        // $excluded = ['manage_billing', 'delete_restaurant'];

        $excluded = []; // Giving full access for now as per "run" mode

        return array_values(array_diff($all, $excluded));
    }
}
