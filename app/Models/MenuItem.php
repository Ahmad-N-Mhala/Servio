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

use Illuminate\Database\Eloquent\SoftDeletes;

class MenuItem extends Model
{
    use HasFactory, HasTranslations, HasRestaurant, SoftDeletes;

    public $translatable = ['name'];

    protected $fillable = [
        'restaurant_id',
        'menu_category_id',
        'type', // item, meal
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
        'sku',
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
        return $this->belongsTo(MenuCategory::class, 'menu_category_id')->withTrashed();
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function extras(): HasMany
    {
        return $this->hasMany(MenuItemExtra::class);
    }

    public function bundles(): HasMany
    {
        return $this->hasMany(MenuItemBundle::class, 'parent_menu_item_id');
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

        // 1. If Meal, check bundled items
        if (($this->type ?? 'item') === 'meal') {
            if ($this->relationLoaded('bundles')) {
                foreach ($this->bundles as $bundle) {
                    if ($bundle->relationLoaded('childItem') && $bundle->childItem) {
                        // Recursively check child status (this calls getInventoryStatusAttribute on child)
                        $childStatus = $bundle->childItem->inventory_status;
                        if ($childStatus['sold_out']) {
                            $status['sold_out'] = true;
                            $status['missing_ingredients'][] = $bundle->childItem->name['en'] ?? $bundle->childItem->name; // Simplified name access
                        }
                    }
                }
            }
            // Extras don't usually block availability unless critical, but usually optional.
            return $status;
        }

        // 2. If Item, check ingredients
        // Ensure ingredients are loaded to check stock
        if ($this->relationLoaded('ingredients')) {
            $ingredientsMap = $this->ingredients->keyBy('id');
            $recipe = $this->recipe ?? [];

            // A. Check Recipe (New Way)
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
            // B. Check Legacy Pivot (Old Way)
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

