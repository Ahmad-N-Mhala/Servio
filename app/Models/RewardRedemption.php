<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RewardRedemption extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'restaurant_id',
        'reward_id',
        'order_id',
        'points_used',
        'status',
        'code',
        'used_at',
        'expires_at',
        'metadata',
    ];

    protected $casts = [
        'points_used' => 'integer',
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($redemption) {
            if (!$redemption->code) {
                $redemption->code = strtoupper(Str::random(8));
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function reward(): BelongsTo
    {
        return $this->belongsTo(Reward::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function markAsUsed(?string $orderId = null): void
    {
        $this->update([
            'status' => 'applied',
            'used_at' => now(),
            'order_id' => $orderId,
        ]);
    }
}

