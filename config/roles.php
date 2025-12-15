<?php

return [
    /*
    |--------------------------------------------------------------------------
    | System Roles
    |--------------------------------------------------------------------------
    |
    | This file defines all available roles in the RestoFy system.
    | This is the single source of truth for roles across the application.
    |
    */

    'roles' => [
        'owner' => [
            'name' => 'Restaurant Owner',
            'description' => 'Full access to all restaurant features',
            'level' => 100,
        ],
        'manager' => [
            'name' => 'Manager',
            'description' => 'Manage daily operations and staff',
            'level' => 80,
        ],
        'head_chef' => [
            'name' => 'Head Chef',
            'description' => 'Manage kitchen operations and menu',
            'level' => 70,
        ],
        'kitchen_staff' => [
            'name' => 'Kitchen Staff',
            'description' => 'Prepare orders and update kitchen status',
            'level' => 50,
        ],
        'waiter' => [
            'name' => 'Waiter',
            'description' => 'Take orders and serve customers',
            'level' => 40,
        ],
        'cashier' => [
            'name' => 'Cashier',
            'description' => 'Handle payments and POS operations',
            'level' => 40,
        ],
        'delivery_driver' => [
            'name' => 'Delivery Driver',
            'description' => 'Handle delivery orders',
            'level' => 30,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Role Validation Rule
    |--------------------------------------------------------------------------
    |
    | Use this in validation rules to ensure consistency
    |
    */

    'validation_rule' => 'in:owner,manager,head_chef,kitchen_staff,waiter,cashier,delivery_driver',

    /*
    |--------------------------------------------------------------------------
    | Role Display Names
    |--------------------------------------------------------------------------
    |
    | Simple key-value pairs for dropdowns and displays
    |
    */

    'display_names' => [
        'owner' => 'Restaurant Owner',
        'manager' => 'Manager',
        'head_chef' => 'Head Chef',
        'kitchen_staff' => 'Kitchen Staff',
        'waiter' => 'Waiter',
        'cashier' => 'Cashier',
        'delivery_driver' => 'Delivery Driver',
    ],
];
