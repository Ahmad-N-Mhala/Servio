<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Traits\HasRestaurant;

class DeliveryIntegration extends Model
{
    use HasFactory, HasRestaurant;

    protected $fillable = [
        'restaurant_id',
        'provider',
        'api_key',
        'api_secret',
        'store_id',
        'webhook_secret',
        'settings',
        'is_enabled',
        'auto_accept_orders',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_enabled' => 'boolean',
        'auto_accept_orders' => 'boolean',
        'api_secret' => 'encrypted', // Encrypt sensitive data
        'api_key' => 'encrypted',
        'webhook_secret' => 'encrypted',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Get the delivery provider definition for this integration
     */
    public function deliveryProvider(): BelongsTo
    {
        return $this->belongsTo(DeliveryProvider::class, 'provider', 'slug');
    }
}
