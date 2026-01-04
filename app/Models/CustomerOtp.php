<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class CustomerOtp extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'phone',
        'otp',
        'expires_at',
        'is_used',
        'type', // e.g., 'redemption'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
