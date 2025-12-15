<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;
use App\Traits\HasRestaurant;

class Ingredient extends Model
{
    use HasFactory, HasTranslations, HasRestaurant;

    public $translatable = ['name'];

    protected $fillable = [
        'restaurant_id',
        'name',
        'unit',
        'current_stock',
        'cost',
        'reorder_level',
        'is_active',
    ];

    protected $casts = [
        'current_stock' => 'decimal:4',
        'cost' => 'decimal:2',
        'reorder_level' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public function menuItems(): BelongsToMany
    {
        return $this->belongsToMany(MenuItem::class, 'menu_item_ingredients')
            ->withPivot('quantity')
            ->withTimestamps();
    }
}
