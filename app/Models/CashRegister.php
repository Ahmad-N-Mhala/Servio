<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MongoDB\Laravel\Eloquent\Model;

class CashRegister extends Model
{
    protected $fillable = [
        'restaurant_id',
        'user_id',
        'opening_balance',
        'closing_balance',
        'expected_balance',
        'difference',
        'opened_at',
        'closed_at',
        'status',
        'opening_notes',
        'closing_notes',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'expected_balance' => 'decimal:2',
        'difference' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CashTransaction::class);
    }

    /**
     * Get current balance based on transactions
     */
    public function getCurrentBalance(): float
    {
        $lastTransaction = $this->transactions()
            ->latest()
            ->first();

        return $lastTransaction ? (float) $lastTransaction->balance_after : (float) $this->opening_balance;
    }

    /**
     * Check if register is open
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }
}
