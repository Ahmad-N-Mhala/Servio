<?php

namespace App\Traits;

use App\Models\Restaurant;
use App\Models\Scopes\RestaurantScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasRestaurant
{
    protected static function booted()
    {
        static::addGlobalScope(new RestaurantScope);

        static::creating(function ($model) {
            if (! array_key_exists('restaurant_id', $model->getAttributes()) && session()->has('active_restaurant_id')) {
                $model->restaurant_id = session('active_restaurant_id');
            }
        });
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }
}
