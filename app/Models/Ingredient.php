<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\HasRestaurant;

class Ingredient extends Model
{
    use HasFactory, HasRestaurant;

    protected $fillable = [
        'restaurant_id',
        'name',
        'unit',
        'current_stock',
        'cost',
        'reorder_level',
        'is_active',
        'notification_user_id',
        'low_stock_notification_sent',
    ];

    protected $casts = [
        'name' => 'array',
        'current_stock' => 'decimal:4',
        'cost' => 'decimal:2',
        'reorder_level' => 'decimal:4',
        'is_active' => 'boolean',
        'low_stock_notification_sent' => 'boolean',
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
     * Boot method to add model event listeners
     */
    protected static function boot()
    {
        parent::boot();

        // Prevent negative stock
        static::saving(function ($ingredient) {
            if ($ingredient->current_stock < 0) {
                throw new \Exception("Stock for ingredient '{$ingredient->name}' cannot be negative. Current: {$ingredient->current_stock}");
            }
        });
    }

    /**
     * Atomically decrement stock (thread-safe for concurrent orders)
     * 
     * @param float $quantity Amount to deduct
     * @return bool Success status
     * @throws \Exception if insufficient stock
     */
    public function decrementStock(float $quantity): bool
    {
        // Use MongoDB's atomic decrement operation
        $result = $this->decrement('current_stock', $quantity);

        // Refresh to get updated value
        $this->refresh();

        // Check if stock went negative (race condition check)
        if ($this->current_stock < 0) {
            // Rollback by incrementing
            $this->increment('current_stock', $quantity);
            throw new \Exception("Insufficient stock for ingredient '" . ($this->name['en'] ?? json_encode($this->name)) . "'. Available: " . ($this->current_stock + $quantity) . ", Required: {$quantity}");
        }

        return $result;
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
