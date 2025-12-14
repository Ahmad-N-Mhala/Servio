<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

use App\Traits\HasRestaurant;

class EarningMethod extends Model
{
    use HasFactory, HasTranslations, HasRestaurant;

    protected $fillable = [
        'restaurant_id',
        'name',
        'description',
        'type',
        'points',
        'currency_amount',
        'min_spent',
        'max_points',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'points' => 'integer',
        'currency_amount' => 'decimal:2',
        'min_spent' => 'decimal:2',
        'max_points' => 'integer',
    ];

    public $translatable = ['name'];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
