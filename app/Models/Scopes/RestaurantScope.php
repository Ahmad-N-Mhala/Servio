<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class RestaurantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (auth()->check() && auth()->user()->is_super_admin) {
            return;
        }

        if (session()->has('active_restaurant_id')) {
            $builder->where('restaurant_id', session('active_restaurant_id'));
        }
    }
}
