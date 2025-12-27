#!/bin/bash

echo "================================================"
echo "Updating Subscription Plans with Correct Features"
echo "================================================"
echo ""

php artisan tinker --execute="
echo 'Updating plans with correct feature keys...' . PHP_EOL;
echo '---' . PHP_EOL;

// Update Free Plan
\$free = \App\Models\Plan::where('slug', 'free')->first();
if (\$free) {
    \$free->features = [
        'menu_management',
        'pos_system',
        'order_management',
        'customer_loyalty',
    ];
    \$free->save();
    echo '✅ Updated Free Plan' . PHP_EOL;
    echo '   Features: ' . implode(', ', \$free->features) . PHP_EOL;
} else {
    echo '❌ Free plan not found' . PHP_EOL;
}
echo '---' . PHP_EOL;

// Update Basic Plan
\$basic = \App\Models\Plan::where('slug', 'basic')->first();
if (\$basic) {
    \$basic->features = [
        'menu_management',
        'pos_system',
        'order_management',
        'qr_ordering',
        'table_management',
        'customer_loyalty',
        'customer_management',
        'inventory_management',
        'staff_management',
        'reports_analytics',
    ];
    \$basic->save();
    echo '✅ Updated Basic Plan' . PHP_EOL;
    echo '   Features: ' . count(\$basic->features) . ' features' . PHP_EOL;
} else {
    echo '❌ Basic plan not found' . PHP_EOL;
}
echo '---' . PHP_EOL;

// Update Pro Plan
\$pro = \App\Models\Plan::where('slug', 'pro')->first();
if (\$pro) {
    \$pro->features = [
        'menu_management',
        'pos_system',
        'order_management',
        'qr_ordering',
        'table_management',
        'kds',
        'customer_loyalty',
        'customer_management',
        'inventory_management',
        'waste_management',
        'staff_management',
        'reports_analytics',
        'financial_management',
        'communication',
    ];
    \$pro->save();
    echo '✅ Updated Pro Plan' . PHP_EOL;
    echo '   Features: ' . count(\$pro->features) . ' features' . PHP_EOL;
} else {
    echo '❌ Pro plan not found' . PHP_EOL;
}
echo '---' . PHP_EOL;

// Update Enterprise Plan
\$enterprise = \App\Models\Plan::where('slug', 'enterprise')->first();
if (\$enterprise) {
    \$enterprise->features = [
        'menu_management',
        'pos_system',
        'order_management',
        'qr_ordering',
        'table_management',
        'kds',
        'customer_loyalty',
        'customer_management',
        'inventory_management',
        'waste_management',
        'staff_management',
        'reports_analytics',
        'financial_management',
        'delivery_integration',
        'communication',
        'multi_restaurant',
        'api_access',
    ];
    \$enterprise->save();
    echo '✅ Updated Enterprise Plan' . PHP_EOL;
    echo '   Features: ' . count(\$enterprise->features) . ' features' . PHP_EOL;
} else {
    echo '❌ Enterprise plan not found' . PHP_EOL;
}
echo '---' . PHP_EOL;

echo PHP_EOL;
echo '✅ All plans updated successfully!' . PHP_EOL;
echo 'Total features in config: ' . count(config('features')) . PHP_EOL;
"

echo ""
echo "================================================"
echo "✅ Plans Updated!"
echo "================================================"
echo ""
echo "Next steps:"
echo "1. Go to Admin > Plans to verify"
echo "2. Edit a plan to see new features"
echo "3. Create a new plan to test"
echo ""
