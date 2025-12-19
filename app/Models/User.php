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
        return $this->belongsToMany(Restaurant::class, 'restaurant_user', 'email', 'restaurant_id', 'email', 'id');
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

        // For regular users, find restaurants via the pivot collection 'restaurant_user'
        // Get all restaurant IDs for this user email
        // Note: In MongoDB, we query the 'restaurant_user' collection directly
        $allowedRestaurantIds = \Illuminate\Support\Facades\DB::table('restaurant_user')
            ->where('email', $this->email)
            ->pluck('restaurant_id')
            ->map(function ($id) {
                return (string) $id;
            })
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

}

