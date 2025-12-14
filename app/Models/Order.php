<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\LoyaltyService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'customer_id',
        'order_number',
        'status',
        'subtotal',
        'tax',
        'total',
        'currency',
        'customer_name',
        'customer_phone',
        'notes',
        'type',
        'table_id',
        'points_earned',
        'points_redeemed',
        'completed_at',
        'delivery_provider',
        'delivery_order_id',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'points_earned' => 'integer',
        'points_redeemed' => 'integer',
        'completed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::updated(function ($order) {
            // Process loyalty points when order status changes to completed
            if ($order->isDirty('status') && $order->status === 'completed' && !$order->points_earned) {
                app(LoyaltyService::class)->processOrderPoints($order);
            }
        });
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }
}
