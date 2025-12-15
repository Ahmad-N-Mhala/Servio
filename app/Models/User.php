<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'is_super_admin',
        'password',
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

        // For regular users, get the restaurant from the active session or their first restaurant
        $restaurantId = session('active_restaurant_id');

        if ($restaurantId) {
            // Verify user has access to this restaurant
            $restaurant = Restaurant::whereExists(function ($query) {
                $query->select(\DB::raw(1))
                    ->from('restaurant_user')
                    ->whereColumn('restaurant_user.restaurant_id', 'restaurants.id')
                    ->where('restaurant_user.email', $this->email);
            })->find($restaurantId);

            if ($restaurant) {
                return $restaurant;
            }
        }

        // Fall back to first restaurant user has access to
        return Restaurant::whereExists(function ($query) {
            $query->select(\DB::raw(1))
                ->from('restaurant_user')
                ->whereColumn('restaurant_user.restaurant_id', 'restaurants.id')
                ->where('restaurant_user.email', $this->email);
        })->first();
    }

}

