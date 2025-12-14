<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

use App\Traits\HasRestaurant;

class MenuItem extends Model
{
    use HasFactory, HasTranslations, HasRestaurant;

    public $translatable = ['name'];

    protected $fillable = [
        'restaurant_id',
        'menu_category_id',
        'name',
        'description',
        'price',
        'currency',
        'image',
        'is_available',
        'sort_order',
        'allergens',
    ];

    protected $casts = [
        'name' => 'array',
        'price' => 'decimal:2',
        'is_available' => 'boolean',
        'sort_order' => 'integer',
        'allergens' => 'array',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class, 'menu_category_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}

