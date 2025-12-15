<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasRestaurant;

class WasteLog extends Model
{
    use HasFactory, HasRestaurant;

    protected $fillable = [
        'restaurant_id',
        'menu_item_id',
        'log_date',
        'added_amount',
        'waste_amount',
        'cost_per_unit',
        'total_loss',
        'notes',
    ];

    protected $casts = [
        'log_date' => 'date',
        'cost_per_unit' => 'decimal:2',
        'total_loss' => 'decimal:2',
    ];

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }
}
