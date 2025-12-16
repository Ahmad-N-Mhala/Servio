<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasRestaurant;

class WasteLog extends Model
{
    use HasFactory, SoftDeletes, HasRestaurant;

    protected $fillable = [
        'restaurant_id',
        'menu_item_id',
        'ingredient_id',
        'log_date',
        'added_amount',
        'waste_amount',
        'cost_per_unit',
        'total_loss',
        'notes',
        'user_id',
        'ingredient_batch_id',
        'stock_before',
        'stock_after',
    ];

    protected $casts = [
        'log_date' => 'date',
        'cost_per_unit' => 'decimal:2',
        'total_loss' => 'decimal:2',
        'stock_before' => 'decimal:4',
        'stock_after' => 'decimal:4',
    ];

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function batch()
    {
        return $this->belongsTo(IngredientBatch::class, 'ingredient_batch_id');
    }
}
