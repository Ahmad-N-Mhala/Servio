<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class LandingScreenshot extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'landing_screenshots';

    protected $fillable = [
        'image_path',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
