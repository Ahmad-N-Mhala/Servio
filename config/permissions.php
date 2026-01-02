<?php

return [
    'dashboard' => [
        'label' => 'Dashboard',
        'permissions' => ['view_dashboard', 'view_analytics', 'export_reports']
    ],
    'pos' => [
        'label' => 'POS System',
        'permissions' => ['view_pos', 'pos_system', 'create_order', 'discount_order', 'void_order', 'view_cash_register_history']
    ],
    'orders' => [
        'label' => 'Order Management',
        'permissions' => ['view_orders', 'create_order', 'edit_order', 'cancel_order', 'delete_order', 'print_bill', 'view_order_status_screen', 'manage_order_status_screen']
    ],
    'kitchen' => [
        'label' => 'Kitchen Display',
        'permissions' => ['view_kitchen', 'update_item_status']
    ],
    'menu' => [
        'label' => 'Menu Management',
        'permissions' => ['view_menu', 'create_category', 'edit_category', 'delete_category', 'create_item', 'edit_item', 'delete_item']
    ],
    'tables' => [
        'label' => 'Table Management',
        'permissions' => ['view_tables', 'create_table', 'edit_table', 'delete_table']
    ],
    'customers' => [
        'label' => 'Customer Management',
        'permissions' => ['view_customers', 'create_customer', 'edit_customer', 'delete_customer']
    ],
    'staff' => [
        'label' => 'Staff Management',
        'permissions' => ['view_staff', 'create_staff', 'edit_staff', 'delete_staff']
    ],
    'inventory' => [
        'label' => 'Inventory & Stock',
        'permissions' => ['view_inventory', 'add_stock', 'deduct_stock', 'delete_inventory']
    ],
    'waste' => [
        'label' => 'Waste Management',
        'permissions' => ['view_waste', 'record_waste', 'delete_waste']
    ],
    'loyalty' => [
        'label' => 'Loyalty Program',
        'permissions' => ['view_loyalty', 'manage_rewards', 'manage_earning_rules', 'adjust_points']
    ],
    'delivery' => [
        'label' => 'Delivery Integrations',
        'permissions' => ['view_delivery_settings', 'toggle_providers']
    ],
    'communication' => [
        'label' => 'Communication & Marketing',
        'permissions' => ['view_communication', 'purchase_sms_bundles', 'manage_templates']
    ],
    'finance' => [
        'label' => 'Finance & Reports',
        'permissions' => ['view_sales_reports', 'view_expense_reports']
    ],
    'settings' => [
        'label' => 'System Settings',
        'permissions' => ['view_settings', 'manage_billing', 'edit_restaurant', 'customize_receipt_template']
    ],
    'service' => [
        'label' => 'Waiter Service',
        'permissions' => ['deliver_orders']
    ],
];
