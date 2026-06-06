<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasRestaurant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MongoDB\Laravel\Eloquent\Model;

class CommunicationTemplate extends Model
{
    use HasFactory, HasRestaurant;

    protected $fillable = [
        'restaurant_id',
        'name',
        'channels',
        'trigger_event',
        'subject',
        'content',
        'sms_content',
        'subject_en',
        'subject_ar',
        'content_en',
        'content_ar',
        'sms_content_en',
        'sms_content_ar',
        'conditions',
        'is_active',
        'timing_type',
        'timing_days',
        'timing_time',
        'reward_config',
    ];

    protected $casts = [
        'channels' => 'array',
        'conditions' => 'array',
        'reward_config' => 'array',
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
