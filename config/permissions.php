<?php

return [
    'dashboard' => [
        'label' => 'Dashboard',
        'permissions' => ['view_dashboard', 'view_analytics']
    ],
    'pos' => [
        'label' => 'POS System',
        'permissions' => ['pos_system', 'view_pos', 'create_order', 'discount_order', 'void_order']
    ],
    'orders' => [
        'label' => 'Order Management',
        'permissions' => ['view_orders', 'edit_order', 'cancel_order', 'delete_order', 'print_bill']
    ],
    'kitchen' => [
        'label' => 'Kitchen Display',
        'permissions' => ['view_kitchen', 'update_item_status', 'complete_order']
    ],
    'menu' => [
        'label' => 'Menu Management',
        'permissions' => ['menu_management', 'view_menu', 'create_category', 'edit_category', 'delete_category', 'create_item', 'edit_item', 'delete_item']
    ],
    'tables' => [
        'label' => 'Table Management',
        'permissions' => ['view_tables', 'create_table', 'edit_table', 'delete_table', 'manage_zones']
    ],
    'customers' => [
        'label' => 'Customer Management',
        'permissions' => ['view_customers', 'create_customer', 'edit_customer', 'delete_customer']
    ],
    'staff' => [
        'label' => 'Staff Management',
        'permissions' => ['view_staff', 'create_staff', 'edit_staff', 'delete_staff', 'manage_permissions']
    ],
    'inventory' => [
        'label' => 'Inventory & Stock',
        'permissions' => ['view_inventory', 'add_stock', 'deduct_stock', 'manage_suppliers', 'view_waste', 'record_waste']
    ],
    'loyalty' => [
        'label' => 'Loyalty Program',
        'permissions' => ['view_loyalty', 'manage_rewards', 'manage_earning_rules', 'adjust_points']
    ],
    'delivery' => [
        'label' => 'Delivery Integrations',
        'permissions' => ['view_delivery_settings', 'toggle_providers', 'manage_menus_sync']
    ],
    'communication' => [
        'label' => 'Communication & Marketing',
        'permissions' => ['view_communication', 'purchase_sms_bundles', 'send_campaigns', 'manage_templates']
    ],
    'finance' => [
        'label' => 'Finance & Reports',
        'permissions' => ['view_sales_reports', 'view_expense_reports', 'view_staff_performance', 'export_reports']
    ],
    'settings' => [
        'label' => 'System Settings',
        'permissions' => ['view_settings', 'update_restaurant_profile', 'manage_billing', 'manage_printers']
    ],
];
