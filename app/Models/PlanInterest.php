<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class PlanInterest extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'restaurant_name',
        'plan_id',
        'plan_name',
        'message',
        'status',
        'admin_notes',
    ];
}
