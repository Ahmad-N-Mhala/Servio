<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Models\Restaurant;
use App\Models\Order;
use App\Models\Customer;

class Feedback extends Model
{
    protected $fillable = [
        'restaurant_id',
        'order_id',
        'customer_id',
        'rating',
        'comment',
        'status',
        'redirected_to_google'
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}