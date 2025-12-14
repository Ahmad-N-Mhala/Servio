<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

use App\Traits\HasRestaurant;

class MenuCategory extends Model
{
    use HasFactory, HasTranslations, HasRestaurant;

    public $translatable = ['name'];

    protected $fillable = [
        'restaurant_id',
        'name',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'name' => 'array',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }
}

