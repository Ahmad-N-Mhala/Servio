<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class MenuItemIngredient extends Model
{
    protected $table = 'menu_item_ingredients';

    protected $fillable = [
        'menu_item_id',
        'ingredient_id',
        'quantity',
    ];
}
