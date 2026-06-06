<?php

namespace App\Models;

use App\Traits\HasRestaurant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class EarningMethod extends Model
{
    use HasFactory, HasRestaurant, HasTranslations;

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
        'currency_amount' => 'float',
        'min_spent' => 'float',
        'max_points' => 'integer',
    ];

    public $translatable = ['name'];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
