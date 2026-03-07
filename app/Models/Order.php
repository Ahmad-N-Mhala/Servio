<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\LoyaltyService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Traits\HasRestaurant;

class Order extends Model
{
    use HasFactory, HasRestaurant;

    protected $fillable = [
        'restaurant_id',
        'customer_id',
        'order_number',
        'status',
        'subtotal',
        'discount_amount',
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
        'payment_status',
        'payment_method',
        'waiter_id',
        'additional_charge',
        'discount_type',
        'discount_value',
        'additional_charge_type',
        'additional_charge_value',
        'transaction_number',
        'feedback_token',
        'source',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'additional_charge' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'additional_charge_value' => 'decimal:2',
        'points_earned' => 'integer',
        'points_redeemed' => 'integer',
        'completed_at' => 'datetime',
        'transaction_number' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::updated(function ($order) {
            // Process loyalty points when order is PAID (regardless of status)
            $isPaid = $order->payment_status === 'paid';
            // Trigger if payment status changed to paid, or if it is paid and we haven't processed points yet
            // We use isDirty just to be safe and avoid loops, but checking points_earned === null safeguards it too.
            $justBecamePaid = $order->isDirty('payment_status') && $isPaid;

            if (($justBecamePaid || ($isPaid && $order->points_earned === null))) {
                app(LoyaltyService::class)->processOrderPoints($order);
            }
        });
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function scopeExcludeUnpaidQr($query)
    {
        return $query->where(function ($q) {
            $q->where(function ($sub) {
                $sub->where('source', '!=', 'qr_code')
                    ->where('order_number', 'not like', 'QR-%');
            })->orWhere('payment_status', 'paid');
        });
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

    public function waiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'waiter_id');
    }
}
