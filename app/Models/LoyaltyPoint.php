<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'balance',
        'total_earned',
        'total_redeemed',
    ];

    protected $casts = [
        'balance' => 'integer',
        'total_earned' => 'integer',
        'total_redeemed' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function addPoints(int $points, string $description = null, ?int $orderId = null, ?\DateTime $expiresAt = null): PointTransaction
    {
        $this->increment('balance', $points);
        $this->increment('total_earned', $points);

        return PointTransaction::create([
            'customer_id' => $this->customer_id,
            'order_id' => $orderId,
            'type' => 'earned',
            'points' => $points,
            'description' => $description ?? 'Points earned from order',
            'balance_after' => $this->balance,
            'expires_at' => $expiresAt,
        ]);
    }

    public function redeemPoints(int $points, string $description = null, ?int $redemptionId = null): PointTransaction
    {
        if ($this->balance < $points) {
            throw new \Exception('Insufficient points');
        }

        $this->decrement('balance', $points);
        $this->increment('total_redeemed', $points);

        return PointTransaction::create([
            'customer_id' => $this->customer_id,
            'reward_redemption_id' => $redemptionId,
            'type' => 'redeemed',
            'points' => -$points,
            'description' => $description ?? 'Points redeemed',
            'balance_after' => $this->balance,
        ]);
    }
}

