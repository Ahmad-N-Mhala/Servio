<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$orders = \App\Models\Order::whereNotNull('customer_id')->get();
$uniqueCustomers = $orders->pluck('customer_id')->unique()->count();
$totalCustomerOrders = $orders->count();

$firstOrder = \App\Models\Order::orderBy('created_at', 'asc')->first();
$daysSinceFirstOrder = $firstOrder ? $firstOrder->created_at->diffInDays(now()) : 1;
$yearsOfOperation = max(1, $daysSinceFirstOrder / 365);

echo "Total Customer Orders: " . $totalCustomerOrders . "\n";
echo "Unique Customers: " . $uniqueCustomers . "\n";
echo "Days Since First Order: " . $daysSinceFirstOrder . "\n";
echo "Years of Operation: " . $yearsOfOperation . "\n";
if ($uniqueCustomers > 0) {
    echo "Avg Orders per Customer (Lifetime): " . ($totalCustomerOrders / $uniqueCustomers) . "\n";
    echo "Avg Orders per Customer per Year: " . (($totalCustomerOrders / $uniqueCustomers) / $yearsOfOperation) . "\n";
}
