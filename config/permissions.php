<?php

return [
    'dashboard' => [
        'label' => 'Dashboard',
        'permissions' => ['view_dashboard', 'view_analytics', 'export_reports'],
        'descriptions' => [
            'view_dashboard' => 'View the general dashboard, summaries, and core performance metrics.',
            'view_analytics' => 'View detailed charts (peak hours, payment methods, waste trend).',
            'export_reports' => 'Export dashboard and order metrics reports to Excel.'
        ],
        'links' => [
            'view_dashboard' => '/en/servio/dashboard',
            'view_analytics' => '/en/servio/dashboard/details',
            'export_reports' => '/en/servio/dashboard/export'
        ]
    ],
    'pos' => [
        'label' => 'POS System',
        'permissions' => ['view_pos', 'pos_system', 'create_order', 'discount_order', 'void_order', 'view_cash_register_history'],
        'descriptions' => [
            'view_pos' => 'Open and access the Cashier Point of Sale (POS) workspace.',
            'pos_system' => 'Perform register operations (settle orders, open/close register, deposit/withdraw cash).',
            'create_order' => 'Create normal orders and checkout/pay in the POS screen.',
            'discount_order' => 'Apply discounts to active orders or items in the POS screen.',
            'void_order' => 'Void active orders or clear drafts in the POS screen.',
            'view_cash_register_history' => 'View open/close history and cash flow audits of the registers.'
        ],
        'links' => [
            'view_pos' => '/en/servio/pos',
            'pos_system' => '/en/servio/pos',
            'create_order' => '/en/servio/pos',
            'discount_order' => '/en/servio/pos',
            'void_order' => '/en/servio/pos',
            'view_cash_register_history' => '/en/servio/cash-register/history'
        ]
    ],
    'orders' => [
        'label' => 'Order Management',
        'permissions' => ['view_orders', 'create_order', 'edit_order', 'cancel_order', 'delete_order', 'print_bill', 'view_order_status_screen', 'manage_order_status_screen', 'create_delivery_order'],
        'descriptions' => [
            'view_orders' => 'Access list of historical and active dine-in / takeaway orders.',
            'create_order' => 'Access the back-office order builder interface.',
            'edit_order' => 'Update status, details, or customer info of an order.',
            'cancel_order' => 'Cancel active orders and release table hold status.',
            'delete_order' => 'Permanently delete orders from database logs.',
            'print_bill' => 'Generate and print customer bills and receipts.',
            'view_order_status_screen' => 'View public customer-facing order status queue screen.',
            'manage_order_status_screen' => 'Access the queue manager screen to move orders between Ready and Preparing.',
            'create_delivery_order' => 'Create delivery orders via the POS screen.'
        ],
        'links' => [
            'view_orders' => '/en/servio/orders',
            'create_order' => '/en/servio/orders/create',
            'edit_order' => '/en/servio/orders',
            'cancel_order' => '/en/servio/orders',
            'delete_order' => '/en/servio/orders',
            'print_bill' => '/en/servio/orders',
            'view_order_status_screen' => '/en/servio/orders/status/screen',
            'manage_order_status_screen' => '/en/servio/orders/status/manage',
            'create_delivery_order' => '/en/servio/pos-orders/create'
        ]
    ],
    'kitchen' => [
        'label' => 'Kitchen Display',
        'permissions' => ['view_kitchen', 'update_item_status'],
        'descriptions' => [
            'view_kitchen' => 'Access the Kitchen Display System (KDS) screen for chefs.',
            'update_item_status' => 'Update individual meal item statuses (Preparing, Ready, Served).'
        ],
        'links' => [
            'view_kitchen' => '/en/servio/kitchen',
            'update_item_status' => '/en/servio/kitchen'
        ]
    ],
    'menu' => [
        'label' => 'Menu Management',
        'permissions' => ['view_menu', 'create_category', 'edit_category', 'delete_category', 'create_item', 'edit_item', 'delete_item'],
        'descriptions' => [
            'view_menu' => 'Access the visual menu builder and items list.',
            'create_category' => 'Add new menu sections / categories.',
            'edit_category' => 'Rename or re-order menu categories.',
            'delete_category' => 'Delete menu categories.',
            'create_item' => 'Add new items, upload photos, or import menu templates.',
            'edit_item' => 'Edit details, extras, or pricing of menu items.',
            'delete_item' => 'Delete items from the menu.'
        ],
        'links' => [
            'view_menu' => '/en/servio/menu',
            'create_category' => '/en/servio/menu',
            'edit_category' => '/en/servio/menu',
            'delete_category' => '/en/servio/menu',
            'create_item' => '/en/servio/menu',
            'edit_item' => '/en/servio/menu',
            'delete_item' => '/en/servio/menu'
        ]
    ],
    'tables' => [
        'label' => 'Table Management',
        'permissions' => ['view_tables', 'create_table', 'edit_table', 'delete_table'],
        'descriptions' => [
            'view_tables' => 'View restaurant dining floor layout, tables list, and download QR codes.',
            'create_table' => 'Add new tables or dining zones to the floor plan.',
            'edit_table' => 'Edit table capacity, names, or regenerate QR codes.',
            'delete_table' => 'Delete tables or dining zones.'
        ],
        'links' => [
            'view_tables' => '/en/servio/tables',
            'create_table' => '/en/servio/tables',
            'edit_table' => '/en/servio/tables',
            'delete_table' => '/en/servio/tables'
        ]
    ],
    'customers' => [
        'label' => 'Customer Management',
        'permissions' => ['view_customers', 'create_customer', 'edit_customer', 'delete_customer'],
        'descriptions' => [
            'view_customers' => 'View customer database and historical purchases.',
            'create_customer' => 'Register new customers into the database.',
            'edit_customer' => 'Modify customer contact info and profile.',
            'delete_customer' => 'Delete customer profiles.'
        ],
        'links' => [
            'view_customers' => '/en/servio/customers',
            'create_customer' => '/en/servio/customers',
            'edit_customer' => '/en/servio/customers',
            'delete_customer' => '/en/servio/customers'
        ]
    ],
    'staff' => [
        'label' => 'Staff Management',
        'permissions' => ['view_staff', 'create_staff', 'edit_staff', 'delete_staff'],
        'descriptions' => [
            'view_staff' => 'View list of restaurant employees and their log records.',
            'create_staff' => 'Register new staff and assign their roles.',
            'edit_staff' => 'Edit staff profile details.',
            'delete_staff' => 'Terminate staff access and delete profile.'
        ],
        'links' => [
            'view_staff' => '/en/servio/staff',
            'create_staff' => '/en/servio/staff',
            'edit_staff' => '/en/servio/staff',
            'delete_staff' => '/en/servio/staff'
        ]
    ],
    'inventory' => [
        'label' => 'Inventory & Stock',
        'permissions' => ['view_inventory', 'view_inventory_reminders', 'add_stock', 'deduct_stock', 'delete_inventory'],
        'descriptions' => [
            'view_inventory' => 'Access inventory stock levels catalog and history.',
            'view_inventory_reminders' => 'View active low-stock alerts and purchase prompts.',
            'add_stock' => 'Record inbound stock shipments and adjust counts up.',
            'deduct_stock' => 'Record manual inventory write-offs or adjust counts down.',
            'delete_inventory' => 'Remove inventory items from catalog.'
        ],
        'links' => [
            'view_inventory' => '/en/servio/inventory',
            'view_inventory_reminders' => '/en/servio/inventory/reminders',
            'add_stock' => '/en/servio/inventory',
            'deduct_stock' => '/en/servio/inventory',
            'delete_inventory' => '/en/servio/inventory'
        ]
    ],
    'waste' => [
        'label' => 'Waste Management',
        'permissions' => ['view_waste', 'record_waste', 'delete_waste'],
        'descriptions' => [
            'view_waste' => 'View the list and dashboard of recorded food waste.',
            'record_waste' => 'Record food waste logs or restore wasted stock.',
            'delete_waste' => 'Delete food waste records.'
        ],
        'links' => [
            'view_waste' => '/en/servio/waste',
            'record_waste' => '/en/servio/waste',
            'delete_waste' => '/en/servio/waste'
        ]
    ],
    'loyalty' => [
        'label' => 'Loyalty Program',
        'permissions' => ['view_loyalty', 'manage_rewards', 'manage_earning_rules', 'adjust_points', 'view_sms_logs'],
        'descriptions' => [
            'view_loyalty' => 'View loyalty overview, customer points, and redeem rewards.',
            'manage_rewards' => 'Create/edit reward items, update card design, and edit general loyalty settings.',
            'manage_earning_rules' => 'Setup rules defining how customers earn loyalty points.',
            'adjust_points' => 'Manually adjust points balance for customers.',
            'view_sms_logs' => 'View list of system OTP and loyalty SMS logs.'
        ],
        'links' => [
            'view_loyalty' => '/en/servio/loyalty',
            'manage_rewards' => '/en/servio/loyalty',
            'manage_earning_rules' => '/en/servio/loyalty/earning-methods',
            'adjust_points' => '/en/servio/loyalty',
            'view_sms_logs' => '/en/servio/loyalty/sms-logs'
        ]
    ],
    'delivery' => [
        'label' => 'Delivery Integrations',
        'permissions' => ['view_delivery_settings', 'toggle_providers', 'manage_delivery_orders'],
        'descriptions' => [
            'view_delivery_settings' => 'View delivery integration settings and configuration.',
            'toggle_providers' => 'Enable/disable delivery platforms (e.g., Talabat, Deliveroo) and push menu.',
            'manage_delivery_orders' => 'View delivery tracking and driver status updates.'
        ],
        'links' => [
            'view_delivery_settings' => '/en/servio/integrations/delivery',
            'toggle_providers' => '/en/servio/integrations/delivery',
            'manage_delivery_orders' => '/en/servio/integrations/delivery'
        ]
    ],
    'communication' => [
        'label' => 'Communication & Marketing',
        'permissions' => ['view_communication', 'purchase_sms_bundles', 'manage_templates'],
        'descriptions' => [
            'view_communication' => 'Access marketing templates and campaign dashboard.',
            'purchase_sms_bundles' => 'View and purchase SMS marketing packages.',
            'manage_templates' => 'Create/edit email and SMS marketing templates.'
        ],
        'links' => [
            'view_communication' => '/en/servio/communication',
            'purchase_sms_bundles' => '/en/servio/communication',
            'manage_templates' => '/en/servio/communication'
        ]
    ],
    'finance' => [
        'label' => 'Finance & Reports',
        'permissions' => ['view_sales_reports', 'view_expense_reports'],
        'descriptions' => [
            'view_sales_reports' => 'Access financial summary, charts, and sales reports.',
            'view_expense_reports' => 'Access monthly expenses reports and write-offs.'
        ],
        'links' => [
            'view_sales_reports' => '/en/servio/financial',
            'view_expense_reports' => '/en/servio/monthly-expenses'
        ]
    ],
    'settings' => [
        'label' => 'System Settings',
        'permissions' => ['view_settings', 'view_plans', 'manage_billing', 'edit_restaurant', 'customize_receipt_template'],
        'descriptions' => [
            'view_settings' => 'View system settings page.',
            'view_plans' => 'View subscription tiers and billing options.',
            'manage_billing' => 'Change restaurant plan or subscribe to paid tiers.',
            'edit_restaurant' => 'View and update restaurant profile and configurations.',
            'customize_receipt_template' => 'Customize design layout of customer receipts.'
        ],
        'links' => [
            'view_settings' => '/en/servio/settings/receipt-template',
            'view_plans' => '/en/servio/plans',
            'manage_billing' => '/en/servio/plans',
            'edit_restaurant' => '/en/servio/select-restaurant',
            'customize_receipt_template' => '/en/servio/settings/receipt-template'
        ]
    ],
    'service' => [
        'label' => 'Waiter Service',
        'permissions' => ['deliver_orders'],
        'descriptions' => [
            'deliver_orders' => 'View orders ready to be served or delivered.'
        ],
        'links' => [
            'deliver_orders' => '/en/servio/service/delivery'
        ]
    ],
    'feedback' => [
        'label' => 'Feedback Management',
        'permissions' => ['view_feedback', 'reply_feedback'],
        'descriptions' => [
            'view_feedback' => 'View reviews and feedback submitted by dining customers.',
            'reply_feedback' => 'Respond to or resolve feedback entries.'
        ],
        'links' => [
            'view_feedback' => '/en/servio/feedback',
            'reply_feedback' => '/en/servio/feedback'
        ]
    ],
];
