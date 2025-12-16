<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IngredientBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'ingredient_id',
        'batch_number',
        'quantity_initial',
        'quantity_remaining',
        'cost_per_unit',
        'expiration_date',
    ];

    protected $casts = [
        'quantity_initial' => 'decimal:4',
        'quantity_remaining' => 'decimal:4',
        'cost_per_unit' => 'decimal:2',
        'expiration_date' => 'date',
    ];

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}
