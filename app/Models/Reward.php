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

class Reward extends Model
{
    use HasFactory, HasTranslations, HasRestaurant;

    public $translatable = ['name'];

    protected $fillable = [
        'restaurant_id',
        'name',
        'description',
        'points_required',
        'min_order_value',
        'reward_type',
        'discount_value',
        'menu_item_id',
        'max_redemptions',
        'redemptions_count',
        'valid_from',
        'valid_until',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'name' => 'array',
        'points_required' => 'integer',
        'min_order_value' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'max_redemptions' => 'integer',
        'redemptions_count' => 'integer',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function menuItems(): BelongsToMany
    {
        return $this->belongsToMany(MenuItem::class, 'reward_menu_item');
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(RewardRedemption::class);
    }

    public function isAvailable(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->max_redemptions && $this->redemptions_count >= $this->max_redemptions) {
            return false;
        }

        $now = now();
        if ($this->valid_from && $now->lt(\Illuminate\Support\Carbon::parse($this->valid_from))) {
            return false;
        }

        if ($this->valid_until && $now->gt(\Illuminate\Support\Carbon::parse($this->valid_until))) {
            return false;
        }

        return true;
    }
}

