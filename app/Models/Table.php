<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use App\Traits\HasRestaurant;
use Illuminate\Support\Str;

class Table extends Model
{
    use HasFactory, HasRestaurant;

    protected $table = 'restaurant_tables';

    protected $fillable = [
        'restaurant_id',
        'zone_id',
        'name',
        'capacity',
        'status',
        'location',
        'qr_code_token',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'zone_id' => 'string', // Ensure it casts correctly for MongoDB
    ];

    protected $appends = [
        'qr_code_url',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($table) {
            if (empty($table->qr_code_token)) {
                $table->qr_code_token = Str::random(32);
            }
        });
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    /**
     * Get the QR code URL for this table
     */
    public function getQrCodeUrlAttribute(): string
    {
        return route('qr.menu', ['token' => $this->qr_code_token]);
    }

    /**
     * Generate a new QR code token
     */
    public function regenerateQrCode(): void
    {
        $this->qr_code_token = Str::random(32);
        $this->save();
    }
}
