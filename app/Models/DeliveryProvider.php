<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class DeliveryProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo_url',
        'api_documentation_url',
        'requires_api_key',
        'requires_api_secret',
        'requires_store_id',
        'requires_webhook_secret',
        'configuration_fields',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'requires_api_key' => 'boolean',
        'requires_api_secret' => 'boolean',
        'requires_store_id' => 'boolean',
        'requires_webhook_secret' => 'boolean',
        'configuration_fields' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get all integrations using this provider
     */
    public function integrations()
    {
        return $this->hasMany(DeliveryIntegration::class, 'provider', 'slug');
    }

    /**
     * Scope to get only active providers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
