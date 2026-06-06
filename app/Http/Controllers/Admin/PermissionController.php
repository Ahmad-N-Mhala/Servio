<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    public function index()
    {
        $dbRoles = \App\Models\Role::all();
        $configNames = config('roles.display_names', []);

        $roles = [];
        $rolePermissions = [];
        $locale = app()->getLocale();

        foreach ($dbRoles as $role) {
            $label = null;
            $display = (! empty($role->display_name) && (is_array($role->display_name) || is_object($role->display_name)))
                ? (array) $role->display_name
                : [];

            // 1. Check DB display_name (Current Locale) - User Override
            if (! empty($display[$locale])) {
                $label = $display[$locale];
            }

            // 2. Check Translation File
            if (! $label && \Illuminate\Support\Facades\Lang::has('roles.'.$role->name)) {
                $label = __('roles.'.$role->name);
            }

            // 3. Fallback to DB display_name (English)
            if (! $label && ! empty($display['en'])) {
                $label = $display['en'];
            }

            // 4. Fallback to Config
            if (! $label) {
                $label = $configNames[$role->name] ?? null;
            }

            // 5. Fallback to Name
            if (! $label) {
                $label = ucwords(str_replace('_', ' ', $role->name));
            }

            $roles[$role->name] = $label;
            $rolePermissions[$role->name] = $role->permissions->pluck('name')->toArray();
        }

        $rolesList = [];
        foreach ($dbRoles as $role) {
            $rolesList[] = [
                'name' => $role->name,
                'display_name' => (array) ($role->display_name ?? []),
                'label' => $roles[$role->name] ?? $role->name,
            ];
        }

        $permissions = config('permissions');

        return inertia('Admin/Permissions/Index', [
            'roles' => $roles, // Keep for backward compatibility/quick lookup
            'rolesList' => $rolesList, // For Roles Tab management
            'permissions' => $permissions,
            'rolePermissions' => $rolePermissions,
        ]);
    }

    public function storeRole(Request $request)
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
        ]);

        // Generate system name (slug) from English name
        $name = \Illuminate\Support\Str::slug($validated['name_en'], '_');

        if (\App\Models\Role::where('name', $name)->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['name_en' => 'Role already exists (slug collision). Try a different English name.']);
        }

        \App\Models\Role::create([
            'name' => $name,
            'guard_name' => 'web',
            'display_name' => [
                'en' => $validated['name_en'],
                'ar' => $validated['name_ar'],
            ],
        ]);

        return back()->with('success', 'Role created successfully.');
    }

    public function updateRole(Request $request, $roleName)
    {
        $role = \App\Models\Role::where('name', $roleName)->firstOrFail();

        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
        ]);

        $role->display_name = [
            'en' => $validated['name_en'],
            'ar' => $validated['name_ar'],
        ];
        $role->save();

        return back()->with('success', 'Role updated successfully.');
    }

    public function destroyRole($roleName)
    {
        $role = \App\Models\Role::where('name', $roleName)->firstOrFail();

        if ($role->name === 'owner') {
            return back()->withErrors(['error' => 'Cannot delete the Owner role.']);
        }

        $role->delete();

        // Also cleanup pivot
        DB::connection('mongodb')->table('role_has_permissions')->where('role_id', $role->id)->delete();

        return back()->with('success', 'Role deleted successfully.');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'role' => 'required|string',
            'permissions' => 'required|array',
        ]);

        $roleName = $validated['role'];
        $permissions = $validated['permissions'];

        \Log::info("Updating permissions for role: {$roleName}", ['perms' => $permissions]);

        try {
            $role = \App\Models\Role::findByName($roleName, 'web');
            \Log::info('Role found: '.$role->id.' Name: '.$role->name);

            // Ensure all permissions exist in DB first (just in case)
            $permissionModels = [];
            foreach ($permissions as $permissionName) {
                $permissionModels[] = \App\Models\Permission::findOrCreate($permissionName, 'web');
            }

            // For MongoDB + Spatie, we want to ensure any existing permissions are cleared
            // We use syncPermissions with the array of permission names
            try {
                // Clear all first
                $role->permissions()->detach();
                $role->unset('permission_id'); // Handle the singular variant seen in DB
                $role->unset('permission_ids'); // Handle the plural variant
                $role->save();

                // Now sync the new ones
                if (! empty($permissions)) {
                    $role->syncPermissions($permissions);
                }

                \Log::info("Permissions synced successfully for role: {$roleName}");
            } catch (\Exception $e) {
                // Fallback for MongoDB: Manually set the permission_ids array if syncPermissions fails
                $ids = array_map(fn ($p) => $p->id, $permissionModels);
                $role->permission_ids = $ids;
                $role->save();
                \Log::info("Permissions manually assigned for role: {$roleName} via fallback");
            }

            // Clear cache to apply changes immediately
            app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
            \Log::info('Permissions cache cleared');

        } catch (\Exception $e) {
            \Log::error('Failed to update permissions: '.$e->getMessage());

            return redirect()->back()->withErrors(['error' => 'Failed to update permissions: '.$e->getMessage()]);
        }

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permissions updated successfully for '.$validated['role']);
    }
}
