<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'is_super_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
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

    public function currentRestaurant()
    {
        // If super admin, use the active_restaurant_id from session
        if ($this->is_super_admin) {
            $restaurantId = session('active_restaurant_id');
            if ($restaurantId) {
                return Restaurant::find($restaurantId);
            }
            return null;
        }

        //TODO: 
        // For regular users, find restaurants via the pivot collection 'restaurant_user'
        // Get all restaurant IDs for this user email
        // FIX: Use manual query because the relationship is broken
        $allowedRestaurantIds = \Illuminate\Support\Facades\DB::connection('mongodb')
            ->table('restaurant_user')
            ->where('email', $this->email)
            ->pluck('restaurant_id')
            ->toArray();

        // Check active session
        $activeId = (string) session('active_restaurant_id');

        if ($activeId && in_array($activeId, $allowedRestaurantIds)) {
            return Restaurant::find($activeId);
        }

        // Fallback to first allowed restaurant
        if (!empty($allowedRestaurantIds)) {
            return Restaurant::whereIn('id', $allowedRestaurantIds)->orderBy('id')->first();
        }

        return null;
    }

    public function getPermissionsForCurrentRestaurant()
    {
        if ($this->is_super_admin) {
            return \App\Models\Permission::pluck('name');
        }

        $restaurant = $this->currentRestaurant();
        if (!$restaurant) {
            return collect([]);
        }

        $pivot = \Illuminate\Support\Facades\DB::connection('mongodb')
            ->table('restaurant_user')
            ->where('email', $this->email)
            ->where('restaurant_id', (string) $restaurant->id)
            ->first();

        if (!$pivot || !isset($pivot->role)) {
            return collect([]);
        }

        $role = \App\Models\Role::findByName($pivot->role, 'web');
        return $role ? $role->permissions->pluck('name') : collect([]);
    }

    public function getRestaurantRole()
    {
        if ($this->is_super_admin) {
            return 'Super Admin';
        }

        $restaurant = $this->currentRestaurant();
        if (!$restaurant) {
            return 'User';
        }

        $pivot = \Illuminate\Support\Facades\DB::connection('mongodb')
            ->table('restaurant_user')
            ->where('email', $this->email)
            ->where('restaurant_id', (string) $restaurant->id)
            ->first();

        return $pivot && isset($pivot->role) ? $pivot->role : 'User';
    }

    public function getLandingRoute()
    {
        if ($this->is_super_admin) {
            return route('admin.dashboard');
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
                    return route($routeName);
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        return route('profile.edit'); // Ultimate fallback
    }
}

