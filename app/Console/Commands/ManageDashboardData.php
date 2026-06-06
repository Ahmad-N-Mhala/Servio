<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Ingredient;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Restaurant;
use App\Models\WasteLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ManageDashboardData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dashboard:data {action : "insert" or "delete"}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert or delete detailed dummy data for the dashboard (90 days, big data)';

    public function handle()
    {
        $action = $this->argument('action');

        if (function_exists('tenant') && tenant()) {
            config(['database.connections.tenant.database' => tenant('id')]);
            DB::purge('tenant');
            DB::reconnect('tenant');
            DB::setDefaultConnection('tenant');
        }

        $restaurant = Restaurant::first();

        if (! $restaurant) {
            $this->error('No restaurant found.');

            return;
        }

        if ($action === 'insert') {
            $this->insertData($restaurant);
        } elseif ($action === 'delete') {
            $this->deleteData($restaurant);
        } else {
            $this->error('Action must be either "insert" or "delete".');
        }
    }

    private function insertData($restaurant)
    {
        $this->info('Starting big dummy data insertion (this may take a moment)...');

        // Create 200 dummy customers
        $customers = [];
        for ($i = 0; $i < 200; $i++) {
            $customers[] = Customer::create([
                'restaurant_id' => $restaurant->id,
                'name' => 'Demo Customer '.$i,
                'email' => 'demo'.$i.'@example.com',
                'phone' => '+971-50-DEMO'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'is_active' => true,
            ]);
        }

        $paymentMethods = ['cash', 'cash', 'card', 'card', 'card', 'online', 'online'];
        $statuses = ['completed', 'completed', 'completed', 'completed', 'completed', 'completed', 'completed', 'pending', 'preparing', 'cancelled'];

        $createdMenuItems = MenuItem::where('restaurant_id', $restaurant->id)->limit(20)->get();
        $ingredients = Ingredient::where('restaurant_id', $restaurant->id)->limit(10)->get();

        if ($createdMenuItems->isEmpty()) {
            $this->warn('No menu items found. Orders will be created without items.');
        }

        // Insert over the last 7 days to populate short-term charts very densely
        for ($day = 7; $day >= 0; $day--) {
            // High volume of orders per day to make stats look busy (50 to 120 per day)
            $ordersCount = rand(50, 120);

            // Weekend bump
            $date = Carbon::now()->subDays($day);
            if ($date->isWeekend()) {
                $ordersCount += rand(30, 60);
            }

            for ($i = 0; $i < $ordersCount; $i++) {
                $status = $statuses[array_rand($statuses)];
                $customer = $customers[array_rand($customers)];

                // Realistic business hours with lunch and dinner peaks
                $hourPool = array_merge(
                    array_fill(0, 5, rand(11, 12)),  // Early lunch
                    array_fill(0, 15, rand(13, 14)), // Lunch Peak
                    array_fill(0, 10, rand(15, 17)), // Afternoon
                    array_fill(0, 25, rand(18, 20)), // Dinner Peak
                    array_fill(0, 10, rand(21, 23))  // Late Night
                );

                $orderHour = $hourPool[array_rand($hourPool)];
                $orderDate = $date->copy()->setTime($orderHour, rand(0, 59));

                $completedAt = null;
                $paymentStatus = 'pending';
                if ($status === 'completed') {
                    // completion time between 15 and 45 mins
                    $completedAt = clone $orderDate;
                    $completedAt->addMinutes(rand(15, 45));
                    $paymentStatus = 'paid';
                }

                $orderNumber = 'DEMO-'.strtoupper(substr(uniqid(), -6));

                $order = Order::create([
                    'restaurant_id' => $restaurant->id,
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'customer_phone' => $customer->phone,
                    'order_number' => $orderNumber,
                    'status' => $status,
                    'subtotal' => 0, // will calculate
                    'tax' => 0,
                    'total' => 0,
                    'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                    'payment_status' => $paymentStatus,
                    'completed_at' => $completedAt,
                    'created_at' => $orderDate,
                    'updated_at' => $completedAt ?? $orderDate,
                ]);

                $subtotal = 0;

                if ($createdMenuItems->isNotEmpty()) {
                    $itemCount = rand(1, 5); // 1 to 5 different items per order
                    for ($j = 0; $j < $itemCount; $j++) {
                        $menuItem = $createdMenuItems->random();
                        $qty = rand(1, 3);
                        $price = $menuItem->price ?? rand(20, 80);
                        $itemTotal = $price * $qty;

                        OrderItem::create([
                            'order_id' => $order->id,
                            'menu_item_id' => $menuItem->id,
                            'quantity' => $qty,
                            'unit_price' => $price,
                            'total_price' => $itemTotal,
                            'created_at' => $orderDate,
                            'updated_at' => $orderDate,
                        ]);
                        $subtotal += $itemTotal;
                    }
                } else {
                    $subtotal = rand(50, 300);
                }

                $tax = $subtotal * 0.05;
                $total = $subtotal + $tax;

                $order->update([
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'total' => $total,
                ]);
            }

            // Generate some daily waste logs to show on the Waste Trend chart
            if ($ingredients->isNotEmpty() && rand(1, 100) > 30) { // 70% chance of waste daily
                $wastesCount = rand(1, 5);
                for ($w = 0; $w < $wastesCount; $w++) {
                    $ing = $ingredients->random();
                    $wasteAmt = rand(1, 5) + (rand(0, 99) / 100);
                    $cost = $ing->cost ?? rand(5, 15);
                    WasteLog::create([
                        'restaurant_id' => $restaurant->id,
                        'ingredient_id' => $ing->id,
                        'log_date' => $date->copy()->startOfDay(),
                        'waste_amount' => $wasteAmt,
                        'cost_per_unit' => $cost,
                        'total_loss' => $wasteAmt * $cost,
                        'notes' => 'Demo Waste',
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);
                }
            }
        }

        $this->info('Massive dummy data inserted successfully!');
    }

    private function deleteData($restaurant)
    {
        $this->info('Deleting big dummy data...');

        // Find Orders starting with DEMO-
        $this->info('Cleaning up orders...');
        $orders = Order::where('restaurant_id', $restaurant->id)
            ->where('order_number', 'LIKE', 'DEMO-%')
            ->get();

        $orderIds = $orders->pluck('id');

        if ($orderIds->isNotEmpty()) {
            OrderItem::whereIn('order_id', $orderIds)->delete();
            Order::whereIn('id', $orderIds)->delete();
        }

        // Delete Customers starting with Demo Customer
        $this->info('Cleaning up customers...');
        Customer::where('restaurant_id', $restaurant->id)
            ->where('name', 'LIKE', 'Demo Customer %')
            ->delete();

        // Delete Waste Logs
        $this->info('Cleaning up waste logs...');
        WasteLog::where('restaurant_id', $restaurant->id)
            ->where('notes', 'Demo Waste')
            ->delete();

        $this->info('Dummy data cleaned up successfully!');
    }
}
