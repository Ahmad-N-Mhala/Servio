<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

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
        'name' => 'array',
        'current_stock' => 'decimal:4',
        'cost' => 'decimal:2',
        'reorder_level' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public function menuItems(): BelongsToMany
    {
        return $this->belongsToMany(MenuItem::class, null, 'ingredient_id', 'menu_item_id')
            ->using(MenuItemIngredient::class)
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function batches()
    {
        return $this->hasMany(IngredientBatch::class);
    }

    /**
     * Update ingredient cost based on FIFO (First In, First Out)
     * The cost reflects the oldest batch with remaining stock
     */
    public function updateCostFromFIFO(): void
    {
        $oldestBatchWithStock = IngredientBatch::where('ingredient_id', $this->id)
            ->where('quantity_remaining', '>', 0)
            ->orderBy('created_at', 'asc')
            ->first();

        if ($oldestBatchWithStock) {
            $this->cost = $oldestBatchWithStock->cost_per_unit;
            $this->save();
        }
    }
}
