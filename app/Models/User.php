<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use MongoDB\Laravel\Auth\User as Authenticatable;
use MongoDB\Laravel\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use \App\Traits\TracksDeletes, HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'password_set_at',
        'is_super_admin',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password_set_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function staff()
    {
        return $this->hasOne(Staff::class);
    }

    public function restaurants(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Restaurant::class, 'restaurant_user', 'email', 'restaurant_id', 'email', 'id')
            ->withPivot('role');
    }

    protected $_currentRestaurantCache = false;

    protected $_permissionsCache = false;

    protected $_restaurantRoleCache = false;

    public function currentRestaurant()
    {
        if ($this->_currentRestaurantCache !== false) {
            return $this->_currentRestaurantCache;
        }

        // If super admin, use the active_restaurant_id from session
        if ($this->is_super_admin) {
            $restaurantId = session('active_restaurant_id');
            if ($restaurantId) {
                return $this->_currentRestaurantCache = Restaurant::find($restaurantId);
            }

            return $this->_currentRestaurantCache = null;
        }

        // For regular users, find restaurants via the pivot collection 'restaurant_user'
        $allowedRestaurantIds = \Illuminate\Support\Facades\DB::connection('mongodb')
            ->table('restaurant_user')
            ->where('email', $this->email)
            ->pluck('restaurant_id')
            ->map(fn ($id) => (string) $id)
            ->toArray();

        // Check active session
        $activeId = (string) session('active_restaurant_id');

        if ($activeId && in_array($activeId, $allowedRestaurantIds)) {
            return $this->_currentRestaurantCache = Restaurant::find($activeId);
        }

        // Fallback to first allowed restaurant
        if (! empty($allowedRestaurantIds)) {
            return $this->_currentRestaurantCache = Restaurant::whereIn('id', $allowedRestaurantIds)->orderBy('id')->first();
        }

        return $this->_currentRestaurantCache = null;
    }

    public function getPermissionsForCurrentRestaurant()
    {
        if ($this->_permissionsCache !== false) {
            return $this->_permissionsCache;
        }

        if ($this->is_super_admin) {
            return $this->_permissionsCache = \App\Models\Permission::pluck('name');
        }

        $restaurant = $this->currentRestaurant();
        if (! $restaurant) {
            return $this->_permissionsCache = collect([]);
        }

        // 1. Get User's Role Permissions in this Restaurant
        $pivot = \Illuminate\Support\Facades\DB::connection('mongodb')
            ->table('restaurant_user')
            ->where('email', $this->email)
            ->where('restaurant_id', (string) $restaurant->id)
            ->first();

        if (! $pivot || ! isset($pivot->role)) {
            return $this->_permissionsCache = collect([]);
        }

        $role = \App\Models\Role::findByName($pivot->role, 'web');
        $rolePermissions = $role ? $role->permissions->pluck('name') : collect([]);

        // 2. Get Active Plan Technical Features
        $subscription = \App\Models\RestaurantSubscription::where('restaurant_id', $restaurant->id)
            ->where('status', 'active')
            ->with('plan')
            ->latest()
            ->first();

        $planFeatures = [];
        if ($subscription && $subscription->plan) {
            $rawFeatures = $subscription->plan->enabled_features;
            if (is_array($rawFeatures)) {
                $planFeatures = $rawFeatures;
            } elseif (is_string($rawFeatures)) {
                $planFeatures = json_decode($rawFeatures, true) ?? [];
            }
        }

        // 3. Define Map & Core
        // Essential features that are included in ANY plan
        $coreGroups = ['dashboard', 'orders', 'menu', 'tables', 'customers', 'staff', 'settings', 'service'];

        // Premium features mapped to permission groups
        $featureMap = [
            'pos' => ['pos'],
            'inventory' => ['inventory', 'waste'],
            'loyalty' => ['loyalty'],
            'delivery' => ['delivery'],
            'marketing' => ['communication'],
            'feedback' => ['feedback'],
            'analytics' => ['finance', 'dashboard'], // Dashboard group has reports, finance has sales
            'kds' => ['kitchen'],
            'qr_ordering' => ['tables'],
        ];

        // 4. Build List of ALL Permitted Actions based on Plan + Core
        $allowedActions = collect(['profile.edit']); // Base non-grouped stuff

        // Add Core permissions
        foreach ($coreGroups as $group) {
            $allowedActions = $allowedActions->merge(config("permissions.{$group}.permissions", []));
        }

        // Add Plan-enabled permissions
        foreach ($planFeatures as $featureKey) {
            if (isset($featureMap[$featureKey])) {
                foreach ($featureMap[$featureKey] as $permissionGroup) {
                    $allowedActions = $allowedActions->merge(config("permissions.{$permissionGroup}.permissions", []));
                }
            }
        }

        // 5. Intersect: User can only do what BOTH their Role AND their Plan allow
        return $this->_permissionsCache = $rolePermissions->intersect($allowedActions->unique())->values();
    }

    public function getRestaurantRole()
    {
        if ($this->_restaurantRoleCache !== false) {
            return $this->_restaurantRoleCache;
        }

        if ($this->is_super_admin) {
            return $this->_restaurantRoleCache = 'Super Admin';
        }

        $restaurant = $this->currentRestaurant();
        if (! $restaurant) {
            return $this->_restaurantRoleCache = 'User';
        }

        $pivot = \Illuminate\Support\Facades\DB::connection('mongodb')
            ->table('restaurant_user')
            ->where('email', $this->email)
            ->where('restaurant_id', (string) $restaurant->id)
            ->first();

        return $this->_restaurantRoleCache = $pivot && isset($pivot->role) ? $pivot->role : 'User';
    }

    public function getLandingRoute()
    {
        if ($this->is_super_admin) {
            return \Mcamara\LaravelLocalization\Facades\LaravelLocalization::getLocalizedURL(null, route('admin.dashboard'));
        }

        $perms = $this->getPermissionsForCurrentRestaurant();

        $map = [
            'view_dashboard' => 'dashboard',
            'view_pos' => 'pos.index',
            'view_orders' => 'orders.index',
            'view_kitchen' => 'kitchen.index',
            'view_menu' => 'menu.index',
            'view_tables' => 'tables.index',
            'view_customers' => 'customers.index',
            'view_staff' => 'staff.index',
            'view_inventory' => 'inventory.index',
            'view_waste' => 'waste.index',
            'view_loyalty' => 'loyalty.index',
            'view_delivery_settings' => 'integrations.delivery.index',
            'view_communication' => 'communication.index',
            'view_sales_reports' => 'reports.sales',
            'manage_billing' => 'plans.index',
            'view_settings' => 'profile.edit',
        ];

        foreach ($map as $perm => $routeName) {
            if ($perms->contains($perm)) {
                // Ensure the route exists before returning
                try {
                    return \Mcamara\LaravelLocalization\Facades\LaravelLocalization::getLocalizedURL(null, route($routeName));
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        return \Mcamara\LaravelLocalization\Facades\LaravelLocalization::getLocalizedURL(null, route('dashboard')); // Ultimate fallback
    }

    /**
     * Check if the user has actively set their password.
     */
    public function hasActivePassword(): bool
    {
        return $this->password_set_at !== null;
    }

    /**
     * Mark the password as actively set by the user.
     */
    public function markPasswordAsSet(): void
    {
        $this->password_set_at = now();
        $this->save();
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $resetUrl = route('password.reset', ['token' => $token, 'email' => $this->email]);

        // Try System Template First
        $commService = app(\App\Services\CommunicationService::class);
        $commService->sendNotification('password_reset', $this, [
            'link' => $resetUrl,
        ]);
    }
}
