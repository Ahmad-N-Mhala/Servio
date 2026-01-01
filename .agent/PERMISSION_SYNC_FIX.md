# Permission Assignment Not Saving - Fix

## Problem
When the super admin assigns permissions to a role and clicks "Save Permissions", the changes are not being saved. The logs show an error:

```
Failed to update permissions: There is no permission named `customize_receipt_template` for guard `web`.
```

## Root Cause
The permission `customize_receipt_template` was added to the `config/permissions.php` file but was **not created in the database**. 

When you add a new permission to the config file, it needs to also be created as a record in the `permissions` collection in MongoDB. The Spatie Permission package requires permissions to exist in the database before they can be assigned to roles.

## Solution

### 1. **Immediate Fix** - Created Missing Permission
Created the missing permission in the database:

```php
\App\Models\Permission::create([
    'name' => 'customize_receipt_template',
    'guard_name' => 'web'
]);
```

### 2. **Long-term Solution** - Permission Sync Command
Created an artisan command to automatically sync permissions from config to database:

**File:** `app/Console/Commands/SyncPermissions.php`

**Usage:**
```bash
php artisan permissions:sync
```

**What it does:**
1. ✅ Reads all permissions from `config/permissions.php`
2. ✅ Compares with permissions in database
3. ✅ Creates any missing permissions
4. ✅ Clears permission cache
5. ✅ Shows detailed output of what was created

**Example output:**
```
Syncing permissions from config to database...
Found 59 permissions in config
Found 67 permissions in database
Found 1 missing permissions:
  - customize_receipt_template
Creating missing permissions...
  ✓ Created: customize_receipt_template
Permission cache cleared

✅ Sync complete! Created 1 new permissions.
```

## How to Prevent This Issue

### When Adding New Permissions:

**Step 1:** Add permission to config file
```php
// config/permissions.php
'settings' => [
    'label' => 'System Settings',
    'permissions' => [
        'view_settings',
        'manage_billing',
        'edit_restaurant',
        'customize_receipt_template' // ← New permission
    ]
],
```

**Step 2:** Run the sync command
```bash
php artisan permissions:sync
```

**Step 3:** Clear cache (optional, sync command does this)
```bash
php artisan cache:clear
```

### Automated Approach:

Add the sync command to your deployment process or run it after pulling new code:

```bash
# In your deployment script
composer install
npm install
php artisan permissions:sync  # ← Add this
php artisan migrate
```

## Testing the Fix

### Test 1: Verify Permission Exists
```bash
php artisan tinker
>>> \App\Models\Permission::where('name', 'customize_receipt_template')->exists()
=> true
```

### Test 2: Assign Permission to Role
1. Go to Admin → Permissions
2. Select "Owner" role
3. Check "Customize Receipt Template" under System Settings
4. Click "Save Permissions"
5. **Expected:** Success message, no errors in logs

### Test 3: Verify Assignment
```bash
php artisan tinker
>>> $role = \App\Models\Role::findByName('owner', 'web');
>>> $role->hasPermissionTo('customize_receipt_template')
=> true
```

## Understanding the Permission System

### Config File (`config/permissions.php`)
- Defines the **structure** and **organization** of permissions
- Used for **UI display** (grouping, labels)
- **Not** the source of truth for what permissions exist

### Database (`permissions` collection)
- The **actual source of truth** for permissions
- Required by Spatie Permission package
- Permissions must exist here to be assigned to roles

### The Flow:
```
1. Define in config → For UI organization
2. Create in database → For actual functionality
3. Assign to roles → Via admin interface
4. Check permissions → In middleware/controllers
```

## Common Mistakes to Avoid

### ❌ Wrong: Only adding to config
```php
// config/permissions.php
'permissions' => ['new_permission'] // Added here only
```
**Result:** Permission appears in UI but can't be saved

### ✅ Correct: Add to config + sync to database
```php
// config/permissions.php
'permissions' => ['new_permission']
```
```bash
php artisan permissions:sync
```
**Result:** Permission works correctly

## Debugging Permission Issues

### Check if permission exists in database:
```bash
php artisan tinker
>>> \App\Models\Permission::pluck('name')->toArray()
```

### Check if role has permission:
```bash
php artisan tinker
>>> $role = \App\Models\Role::findByName('owner', 'web');
>>> $role->permissions->pluck('name')->toArray()
```

### Check config permissions:
```bash
php artisan tinker
>>> $perms = [];
>>> foreach (config('permissions') as $m => $d) {
>>>     $perms = array_merge($perms, $d['permissions']);
>>> }
>>> $perms
```

### Find missing permissions:
```bash
php artisan permissions:sync
```

## Files Modified/Created

### Created:
1. **`app/Console/Commands/SyncPermissions.php`** - Permission sync command

### Modified:
1. **Database** - Added `customize_receipt_template` permission

## Best Practices

1. **Always run `permissions:sync`** after adding new permissions to config
2. **Include in deployment scripts** to automate the process
3. **Test permission assignment** after adding new permissions
4. **Clear cache** if permissions don't appear (sync command does this)
5. **Document new permissions** when adding them

## Future Improvements

### Option 1: Automatic Sync on Config Change
Create a service provider that automatically syncs permissions when config changes are detected.

### Option 2: Migration-Based Permissions
Create a migration for each new permission:
```php
// database/migrations/2026_01_01_create_receipt_template_permission.php
public function up() {
    Permission::create(['name' => 'customize_receipt_template', 'guard_name' => 'web']);
}
```

### Option 3: Seeder Integration
Add to `PermissionSeeder`:
```php
$permissions = [
    'customize_receipt_template',
    // ... other permissions
];

foreach ($permissions as $permission) {
    Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
}
```

## Summary

**Problem:** New permission in config but not in database  
**Solution:** Created permission in database + sync command  
**Prevention:** Run `php artisan permissions:sync` after adding permissions  
**Result:** ✅ Permission assignment now works correctly

The permission system is now properly synced and the super admin can successfully assign the `customize_receipt_template` permission (and all other permissions) to roles!
