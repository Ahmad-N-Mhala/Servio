<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Traits\HasRestaurant;

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
