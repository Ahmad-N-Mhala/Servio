<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    public function index()
    {
        $roles = config('roles.display_names');
        $permissions = config('permissions');

        // Get current permissions for each role directly from Spatie models
        $rolePermissions = [];
        foreach (array_keys($roles) as $roleName) {
            $role = \App\Models\Role::findByName($roleName, 'web');
            $rolePermissions[$roleName] = $role ? $role->permissions->pluck('name')->toArray() : [];
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

        $roleName = $validated['role'];
        $permissions = $validated['permissions'];

        try {
            $role = \App\Models\Role::findByName($roleName, 'web');
            
            // Sync permissions directly
            // We need to ensure the permission models exist (they should be seeded, but safe to check)
            $permsToSync = [];
            foreach ($permissions as $permName) {
                $permsToSync[] = $permName;
            }

            $role->syncPermissions($permsToSync);

            // Clear cache to apply changes immediately
            app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to update permissions: ' . $e->getMessage()]);
        }

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permissions updated successfully for ' . $validated['role']);
    }
}
