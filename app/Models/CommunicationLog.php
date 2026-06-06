<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class CommunicationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'recipient',
        'subject',
        'message',
        'status',
        'cost',
        'error_message',
        'communication_template_id',
        'restaurant_id',
        'sent_at',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
    ];

    public function template(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CommunicationTemplate::class, 'communication_template_id');
    }
}
