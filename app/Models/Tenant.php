<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasFactory, HasDatabase, HasDomains;

    protected $fillable = [
        'id',
        'identifier',
        'domain',
        'data',
        'plan_id',
        'subscription_status',
        'subscription_ends_at',
    ];

    protected $casts = [
        'data' => 'array',
        'subscription_ends_at' => 'datetime',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(\App\Models\Subscription::class);
    }

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'identifier',
            'plan_id',
            'subscription_status',
            'subscription_ends_at',
        ];
    }
}

