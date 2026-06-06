<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MongoDB\Laravel\Eloquent\Model;

class MenuItemBundle extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_menu_item_id',
        'child_menu_item_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function parentItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_menu_item_id');
    }

    public function childItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'child_menu_item_id');
    }
}
