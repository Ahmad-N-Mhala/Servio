<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'images',
        'recipe',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_available' => 'boolean',
        'sort_order' => 'integer',
        'allergens' => 'array',
        'images' => 'array',
        'recipe' => 'array', // Stores ingredients directly: [{ingredient_id, quantity}]
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

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'menu_item_ingredients', 'menu_item_id', 'ingredient_id')
            ->using(MenuItemIngredient::class)
            ->withPivot('quantity')
            ->withTimestamps();
    }
    protected $appends = ['inventory_status'];

    public function getInventoryStatusAttribute()
    {
        $status = [
            'sold_out' => false,
            'low_stock' => false,
            'missing_ingredients' => [],
        ];

        // Ensure ingredients are loaded to check stock
        if ($this->relationLoaded('ingredients')) {
            $ingredientsMap = $this->ingredients->keyBy('id');
            $recipe = $this->recipe ?? [];

            // 1. Check Recipe (New Way)
            if (!empty($recipe)) {
                foreach ($recipe as $component) {
                    $ingId = $component['ingredient_id'] ?? null;
                    $qtyNeeded = (float) ($component['quantity'] ?? 0);

                    if ($ingId && $qtyNeeded > 0) {
                        // Find ingredient in loaded relation
                        $ingredient = $ingredientsMap->get($ingId) ?? $ingredientsMap->first(function ($i) use ($ingId) {
                            return (string) $i->id === (string) $ingId;
                        });

                        if ($ingredient) {
                            if ($ingredient->current_stock < $qtyNeeded) {
                                $status['sold_out'] = true;
                                $status['missing_ingredients'][] = $ingredient->name;
                            }
                        }
                    }
                }
            }
            // 2. Check Legacy Pivot (Old Way)
            else {
                foreach ($this->ingredients as $ing) {
                    $qtyNeeded = $ing->pivot->quantity ?? 1;
                    if ($ing->current_stock < $qtyNeeded) {
                        $status['sold_out'] = true;
                        $status['missing_ingredients'][] = $ing->name;
                    }
                }
            }
        }

        return $status;
    }
}

