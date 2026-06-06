<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class StaffLog extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'staff_logs';

    protected $fillable = [
        'staff_id',
        'user_id', // The user who is being logged about
        'action', // 'update', 'login', 'logout'
        'changes', // Array of changes ['field' => ['old' => 'a', 'new' => 'b']]
        'causer_id', // ID of the user performing the action
        'causer_name', // Cached name of the user performing the action
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function causer()
    {
        return $this->belongsTo(User::class, 'causer_id');
    }
}
