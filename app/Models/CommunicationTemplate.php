<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunicationTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'name',
        'channels',
        'trigger_event',
        'subject',
        'content',
        'conditions',
        'is_active',
        'timing_type',
        'timing_days',
        'timing_time',
    ];

    protected $casts = [
        'channels' => 'array',
        'conditions' => 'array',
        'is_active' => 'boolean',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(CommunicationLog::class);
    }
}
