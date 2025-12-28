<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';

    protected $fillable = [
        'name',
        'code',
        'currency',
        'dial_code',
        'rate',
        'states'
    ];

    protected $casts = [
        'states' => 'array',
        'rate' => 'float',
    ];
}
