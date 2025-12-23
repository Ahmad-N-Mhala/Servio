<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class AssignPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Define role permissions mapping
        $rolePermissions = [
            'manager' => [
                'view_dashboard', 'view_analytics',
                'view_orders', 'edit_order', 'cancel_order', 'delete_order', 'print_bill',
                'view_menu', 'create_category', 'edit_category', 'delete_category', 'create_item', 'edit_item', 'delete_item',
                'view_tables', 'create_table', 'edit_table', 'delete_table', 'manage_zones',
                'view_customers', 'create_customer', 'edit_customer', 'delete_customer',
                'view_staff', 'create_staff', 'edit_staff', 'delete_staff', 'manage_permissions',
                'view_inventory', 'add_stock', 'deduct_stock', 'manage_suppliers', 'view_waste', 'record_waste',
                'view_sales_reports', 'view_expense_reports', 'view_staff_performance', 'export_reports',
                'view_settings', 'update_restaurant_profile', 'manage_billing', 'manage_printers'
            ],
            'waiter' => [
                'view_dashboard', // Needed to access dashboard
                'pos_system', 'view_pos', 'create_order', 'discount_order', 'void_order',
                'view_orders', 'edit_order', 'print_bill',
                'view_tables',
                'view_customers', 'create_customer',
            ],
            'kitchen_staff' => [
                'view_dashboard', // Needed to access dashboard
                'view_kitchen', 'update_item_status', 'complete_order',
                'view_orders', // Maybe?
            ],
            'head_chef' => [
                'view_dashboard',
                'view_kitchen', 'update_item_status', 'complete_order',
                'view_menu', 'create_item', 'edit_item', // Can manage menu items
                'view_inventory', 'add_stock', 'deduct_stock', 'view_waste', 'record_waste',
            ],
            'cashier' => [
                'view_dashboard',
                'pos_system', 'view_pos', 'create_order', 'discount_order', 'void_order',
                'view_orders', 'edit_order', 'print_bill',
                'view_customers',
            ],
            'delivery_driver' => [
                'view_dashboard',
                'view_orders',
            ]
        ];

        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                // Sync permissions
                // We need to fetch Permission models first
                $perms = Permission::whereIn('name', $permissions)->get();
                $role->syncPermissions($perms);
                $this->command->info("Assigned " . $perms->count() . " permissions to role: {$roleName}");
            } else {
                $this->command->error("Role not found: {$roleName}");
            }
        }
    }
}
