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
                'permissions' => ['view_dashboard']
            ],
            'pos' => [
                'label' => 'POS System',
                'permissions' => ['view_pos', 'create_order', 'edit_order', 'delete_order']
            ],
            'orders' => [
                'label' => 'Orders',
                'permissions' => ['view_orders', 'edit_orders', 'delete_orders']
            ],
            'kitchen' => [
                'label' => 'Kitchen',
                'permissions' => ['view_kitchen', 'update_order_status']
            ],
            'menu' => [
                'label' => 'Menu Management',
                'permissions' => ['view_menu', 'create_menu_item', 'edit_menu_item', 'delete_menu_item']
            ],
            'tables' => [
                'label' => 'Tables',
                'permissions' => ['view_tables', 'create_table', 'edit_table', 'delete_table']
            ],
            'staff' => [
                'label' => 'Staff Management',
                'permissions' => ['view_staff', 'create_staff', 'edit_staff', 'delete_staff']
            ],
            'loyalty' => [
                'label' => 'Loyalty Program',
                'permissions' => ['view_loyalty', 'manage_rewards', 'manage_earning_methods']
            ],
            'delivery' => [
                'label' => 'Delivery Integrations',
                'permissions' => ['view_delivery', 'manage_delivery_integrations']
            ],
            'communication' => [
                'label' => 'Communication',
                'permissions' => ['view_communication', 'send_messages']
            ],
            'reports' => [
                'label' => 'Reports & Analytics',
                'permissions' => ['view_reports', 'export_reports']
            ],
        ];
    }

    // Define all available roles
    private function getAllRoles()
    {
        return [
            'owner' => 'Restaurant Owner',
            'manager' => 'Manager',
            'waiter' => 'Waiter',
            'chef' => 'Chef',
            'cashier' => 'Cashier',
        ];
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

        DB::table('role_permissions')->updateOrInsert(
            ['role' => $validated['role']],
            [
                'permissions' => json_encode($validated['permissions']),
                'updated_at' => now(),
                'created_at' => DB::raw('COALESCE(created_at, NOW())'),
            ]
        );

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permissions updated successfully for ' . $validated['role']);
    }
}
