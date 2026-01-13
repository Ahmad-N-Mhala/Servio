<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

use App\Traits\HasRestaurant;

class Customer extends Model
{
    use HasFactory, HasRestaurant;

    protected $fillable = [
        'restaurant_id',
        'phone',
        'name',
        'email',
        'birthday',
        'birth_date',
        'preferences',
        'total_orders',
        'total_spent',
        'loyalty_tier',
        'last_order_at',
        'is_active',
    ];

    protected $casts = [
        'preferences' => 'array',
        'total_orders' => 'integer',
        'total_spent' => 'decimal:2',
        'birthday' => 'date',
        'birth_date' => 'date',
        'last_order_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function loyaltyPoints(): HasOne
    {
        return $this->hasOne(LoyaltyPoint::class);
    }

    public function pointTransactions(): HasMany
    {
        return $this->hasMany(PointTransaction::class);
    }

    public function rewardRedemptions(): HasMany
    {
        return $this->hasMany(RewardRedemption::class);
    }

    public function getCurrentPointsAttribute(): int
    {
        return $this->loyaltyPoints?->balance ?? 0;
    }

    public function calculateTier(): string
    {
        $totalSpent = (float) $this->total_spent;

        if ($totalSpent >= 5000) {
            return 'platinum';
        } elseif ($totalSpent >= 2000) {
            return 'gold';
        } elseif ($totalSpent >= 500) {
            return 'silver';
        }

        return 'bronze';
    }

    public function updateTier(): bool
    {
        $newTier = $this->calculateTier();
        if ($newTier !== $this->loyalty_tier) {
            $this->update(['loyalty_tier' => $newTier]);
            return true;
        }
        return false;
    }
}

