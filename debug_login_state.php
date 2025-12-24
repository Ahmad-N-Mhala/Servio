<?php

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Restaurant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

$email = 'waiter@example.com';
$user = User::where('email', $email)->first();

if (!$user) {
    echo "User {$email} not found!\n";
    exit;
}

echo "User Found: {$user->name} ({$user->id})\n";
echo "Super Admin: " . ($user->is_super_admin ? 'Yes' : 'No') . "\n";

// 1. Check Global Roles
echo "\n--- Global Roles ---\n";
$roles = $user->getRoleNames();
echo "Assigned Roles: " . $roles->implode(', ') . "\n";

foreach ($roles as $roleName) {
    $role = Role::findByName($roleName, 'web');
    echo "Permissions for role '{$roleName}':\n";
    echo $role->permissions->pluck('name')->implode(', ') . "\n";
}

// 2. Check Direct Permissions
echo "\n--- Direct Permissions ---\n";
echo $user->getDirectPermissions()->pluck('name')->implode(', ') . "\n";

// 3. Check All Permissions (via Spatie)
echo "\n--- All Permissions (Spatie) ---\n";
echo $user->getAllPermissions()->pluck('name')->implode(', ') . "\n";

// 4. Check Restaurant Association (Pivot)
echo "\n--- Restaurant Association (Pivot) ---\n";
$restaurants = $user->restaurants;
echo "Attached Restaurants: " . $restaurants->count() . "\n";

foreach ($restaurants as $restaurant) {
    echo "Restaurant: {$restaurant->name} ({$restaurant->id})\n";
    echo "Pivot Data (Eloquent): " . json_encode($restaurant->pivot) . "\n";
    
    // Check Raw DB for this association
    $rawPivot = DB::connection('mongodb')->table('restaurant_user')
        ->where('email', $email)
        ->where('restaurant_id', $restaurant->id)
        ->first();
    echo "Pivot Data (Raw DB): " . json_encode($rawPivot) . "\n";
}

// 5. Simulate Gate Check
echo "\n--- Gate Check Simulation ---\n";
Auth::login($user);
$restaurant = $user->restaurants->first();
if ($restaurant) {
    session(['active_restaurant_id' => $restaurant->id]);
    echo "Set active_restaurant_id to: {$restaurant->id}\n";
    
    $canViewDashboard = $user->can('view_dashboard');
    echo "Can 'view_dashboard'? " . ($canViewDashboard ? 'YES' : 'NO') . "\n";
    
    if (!$canViewDashboard) {
        echo "Debug: Why NO?\n";
        // Manually run the logic from AppServiceProvider
        $pivotEntry = DB::connection('mongodb')->table('restaurant_user')
            ->where('email', $user->email)
            ->where('restaurant_id', $restaurant->id)
            ->first();
            
        if ($pivotEntry) {
             $roleName = is_array($pivotEntry) ? $pivotEntry['role'] : $pivotEntry->role;
             echo "Pivot Role Found: {$roleName}\n";
             $role = Role::findByName($roleName, 'web');
             if ($role) {
                 echo "Role '{$roleName}' has 'view_dashboard'? " . ($role->hasPermissionTo('view_dashboard') ? 'Yes' : 'No') . "\n";
             } else {
                 echo "Role '{$roleName}' NOT FOUND in DB.\n";
             }
        } else {
            echo "Pivot Entry NOT FOUND in AppServiceProvider logic.\n";
        }
    }
} else {
    echo "No restaurant to test Gate check against.\n";
}
