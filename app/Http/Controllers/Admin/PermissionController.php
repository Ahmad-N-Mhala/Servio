<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    // Define all available permissions in the system
    private function getAllPermissions()
    {
        return [
            'dashboard' => [
                'label' => 'Dashboard',
                'permissions' => ['view_dashboard', 'view_analytics']
            ],
            'pos' => [
                'label' => 'POS System',
                'permissions' => ['pos_system', 'view_pos', 'create_order', 'discount_order', 'void_order']
            ],
            'orders' => [
                'label' => 'Order Management',
                'permissions' => ['view_orders', 'edit_order', 'cancel_order', 'delete_order', 'print_bill']
            ],
            'kitchen' => [
                'label' => 'Kitchen Display',
                'permissions' => ['view_kitchen', 'update_item_status', 'complete_order']
            ],
            'menu' => [
                'label' => 'Menu Management',
                'permissions' => ['menu_management', 'view_menu', 'create_category', 'edit_category', 'delete_category', 'create_item', 'edit_item', 'delete_item']
            ],
            'tables' => [
                'label' => 'Table Management',
                'permissions' => ['view_tables', 'create_table', 'edit_table', 'delete_table', 'manage_zones']
            ],
            'customers' => [
                'label' => 'Customer Management',
                'permissions' => ['view_customers', 'create_customer', 'edit_customer', 'delete_customer']
            ],
            'staff' => [
                'label' => 'Staff Management',
                'permissions' => ['view_staff', 'create_staff', 'edit_staff', 'delete_staff', 'manage_permissions']
            ],
            'inventory' => [
                'label' => 'Inventory & Stock',
                'permissions' => ['view_inventory', 'add_stock', 'deduct_stock', 'manage_suppliers', 'view_waste', 'record_waste', 'delete_inventory']
            ],
            'loyalty' => [
                'label' => 'Loyalty Program',
                'permissions' => ['view_loyalty', 'manage_rewards', 'manage_earning_rules', 'adjust_points']
            ],
            'delivery' => [
                'label' => 'Delivery Integrations',
                'permissions' => ['view_delivery_settings', 'toggle_providers', 'manage_menus_sync']
            ],
            'communication' => [
                'label' => 'Communication & Marketing',
                'permissions' => ['view_communication', 'purchase_sms_bundles', 'send_campaigns', 'manage_templates']
            ],
            'finance' => [
                'label' => 'Finance & Reports',
                'permissions' => ['view_sales_reports', 'view_expense_reports', 'view_staff_performance', 'export_reports']
            ],
            'settings' => [
                'label' => 'System Settings',
                'permissions' => ['view_settings', 'update_restaurant_profile', 'manage_billing', 'manage_printers']
            ],
        ];
    }

    // Define all available roles
    private function getAllRoles()
    {
        return config('roles.display_names');
    }

    public function index()
    {
        $roles = $this->getAllRoles();
        $permissions = $this->getAllPermissions();

        // Get current permissions for each role from database
        $rolePermissions = [];
        foreach (array_keys($roles) as $role) {
            $stored = DB::table('role_permissions')
                ->where('role', $role)
                ->first();

            $rolePermissions[$role] = $stored ? json_decode($stored->permissions, true) : [];
        }

        return inertia('Admin/Permissions/Index', [
            'roles' => $roles,
            'permissions' => $permissions,
            'rolePermissions' => $rolePermissions,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'role' => 'required|string',
            'permissions' => 'required|array',
        ]);

        $record = DB::table('role_permissions')->where('role', $validated['role'])->first();

        if ($record) {
            DB::table('role_permissions')
                ->where('role', $validated['role'])
                ->update([
                    'permissions' => json_encode($validated['permissions']),
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('role_permissions')->insert([
                'role' => $validated['role'],
                'permissions' => json_encode($validated['permissions']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Sync with Spatie Permissions
        try {
            $roleName = $validated['role'];
            $role = \App\Models\Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            // Ensure permissions exist
            $permissionsToSync = [];
            foreach ($validated['permissions'] as $permName) {
                // Create if not exists to avoid errors
                $permission = \App\Models\Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
                $permissionsToSync[] = $permission;
            }

            $role->syncPermissions($permissionsToSync);

            // Clear cache to apply changes immediately
            app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        } catch (\Exception $e) {
            \Log::error('Failed to sync permissions for role ' . $validated['role'] . ': ' . $e->getMessage());
            // We don't stop the flow, but logging is good
        }

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permissions updated successfully for ' . $validated['role']);
    }
}
