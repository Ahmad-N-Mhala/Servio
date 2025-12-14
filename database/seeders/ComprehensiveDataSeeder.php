<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComprehensiveDataSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure plans exist
        $this->call(PlanSeeder::class);

        // Create 5 restaurants with complete data
        for ($i = 1; $i <= 5; $i++) {
            $this->createRestaurantWithData($i);
        }
    }

    private function createRestaurantWithData($index)
    {
        $restaurantNames = ['The Golden Fork', 'Sunset Bistro', 'Ocean View Cafe', 'Mountain Grill', 'Urban Kitchen'];
        $restaurantName = $restaurantNames[$index - 1] ?? "Restaurant $index";

        // Create owner
        $owner = \App\Models\User::updateOrCreate(
            ['email' => "owner$index@example.com"],
            [
                'name' => "Owner $index",
                'password' => bcrypt('password'),
            ]
        );

        // Create restaurant
        $restaurant = \App\Models\Restaurant::updateOrCreate(
            ['slug' => \Illuminate\Support\Str::slug($restaurantName)],
            [
                'name' => $restaurantName,
                'address' => "$index Main Street, Downtown",
                'phone' => "+1-555-010$index",
                'email' => "contact@restaurant$index.com",
            ]
        );

        // Link owner to restaurant
        DB::table('restaurant_user')->updateOrInsert(
            [
                'restaurant_id' => $restaurant->id,
                'email' => $owner->email,
            ],
            [
                'role' => 'owner',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Create menu categories and items
        $categories = ['Appetizers', 'Main Course', 'Desserts', 'Beverages'];
        foreach ($categories as $catIndex => $categoryName) {
            $category = \App\Models\MenuCategory::create([
                'restaurant_id' => $restaurant->id,
                'name' => ['en' => $categoryName],
                'description' => "Delicious $categoryName",
                'sort_order' => $catIndex,
                'is_active' => true,
            ]);

            // Create 3-5 items per category
            $itemCount = rand(3, 5);
            for ($j = 1; $j <= $itemCount; $j++) {
                \App\Models\MenuItem::create([
                    'restaurant_id' => $restaurant->id,
                    'menu_category_id' => $category->id,
                    'name' => ['en' => $categoryName . " Item $j"],
                    'description' => "Delicious " . strtolower($categoryName) . " item",
                    'price' => rand(5, 50) + 0.99,
                    'currency' => 'USD',
                    'is_available' => true,
                    'sort_order' => $j,
                ]);
            }
        }

        // Create tables
        for ($t = 1; $t <= 10; $t++) {
            \App\Models\Table::create([
                'restaurant_id' => $restaurant->id,
                'name' => "Table $t",
                'capacity' => rand(2, 8),
                'status' => ['available', 'occupied', 'reserved'][rand(0, 2)],
            ]);
        }

        // Create customers
        for ($c = 1; $c <= 20; $c++) {
            $customer = \App\Models\Customer::create([
                'restaurant_id' => $restaurant->id,
                'name' => "Customer $c - " . $restaurantName,
                'email' => "customer$c.r$index@example.com",
                'phone' => "+1-555-" . str_pad(($index * 100) + $c, 4, '0', STR_PAD_LEFT),
                'is_active' => true,
            ]);

            // Create loyalty points for customer
            \App\Models\LoyaltyPoint::create([
                'customer_id' => $customer->id,
                'balance' => rand(0, 500),
            ]);
        }

        // Create orders
        $customers = \App\Models\Customer::where('restaurant_id', $restaurant->id)->get();
        $menuItems = \App\Models\MenuItem::where('restaurant_id', $restaurant->id)->get();
        $tables = \App\Models\Table::where('restaurant_id', $restaurant->id)->get();

        if ($customers->count() > 0 && $menuItems->count() > 0 && $tables->count() > 0) {
            for ($o = 1; $o <= 30; $o++) {
                $customer = $customers->random();
                $table = $tables->random();

                $order = \App\Models\Order::create([
                    'restaurant_id' => $restaurant->id,
                    'customer_id' => $customer->id,
                    'table_id' => $table->id,
                    'order_number' => 'ORD-' . $index . '-' . str_pad($o, 4, '0', STR_PAD_LEFT),
                    'status' => ['pending', 'preparing', 'ready', 'completed'][rand(0, 3)],
                    'type' => ['dine_in', 'takeaway', 'delivery'][rand(0, 2)],
                    'subtotal' => 0,
                    'tax' => 0,
                    'total' => 0,
                    'currency' => 'USD',
                    'customer_name' => $customer->name,
                    'customer_phone' => $customer->phone,
                    'completed_at' => rand(0, 1) ? now()->subDays(rand(1, 30)) : null,
                ]);

                // Add order items
                $itemCount = rand(2, 5);
                $subtotal = 0;
                for ($oi = 0; $oi < $itemCount; $oi++) {
                    $item = $menuItems->random();
                    $quantity = rand(1, 3);
                    $price = $item->price * $quantity;
                    $subtotal += $price;

                    \App\Models\OrderItem::create([
                        'order_id' => $order->id,
                        'menu_item_id' => $item->id,
                        'quantity' => $quantity,
                        'price' => $item->price,
                        'subtotal' => $price,
                        'notes' => rand(0, 1) ? 'Extra sauce' : null,
                    ]);
                }

                // Update order totals
                $tax = $subtotal * 0.1;
                $total = $subtotal + $tax;
                $order->update([
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'total' => $total,
                ]);
            }
        }

        // Create staff members
        for ($s = 1; $s <= 5; $s++) {
            $staffEmail = "staff$s.r$index@example.com";

            // Create user for staff
            $staffUser = \App\Models\User::updateOrCreate(
                ['email' => $staffEmail],
                [
                    'name' => "Staff Member $s - " . $restaurantName,
                    'password' => bcrypt('password'),
                ]
            );

            // Link to restaurant
            DB::table('restaurant_user')->updateOrInsert(
                [
                    'restaurant_id' => $restaurant->id,
                    'email' => $staffEmail,
                ],
                [
                    'role' => ['manager', 'waiter', 'chef', 'cashier'][rand(0, 3)],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Create loyalty rewards
        $rewards = [
            ['name' => 'Free Appetizer', 'points' => 100, 'type' => 'discount', 'value' => 5],
            ['name' => '10% Off', 'points' => 200, 'type' => 'percentage', 'value' => 10],
            ['name' => 'Free Dessert', 'points' => 150, 'type' => 'discount', 'value' => 8],
            ['name' => '$5 Off', 'points' => 250, 'type' => 'fixed', 'value' => 5],
        ];

        foreach ($rewards as $rewardData) {
            \App\Models\Reward::create([
                'restaurant_id' => $restaurant->id,
                'name' => $rewardData['name'],
                'description' => 'Redeem your points for ' . $rewardData['name'],
                'points_required' => $rewardData['points'],
                'reward_type' => $rewardData['type'],
                'reward_value' => $rewardData['value'],
                'is_active' => true,
            ]);
        }

        // Create earning methods
        \App\Models\EarningMethod::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Purchase Points',
            'description' => 'Earn 1 point for every $1 spent',
            'points_per_dollar' => 1,
            'is_active' => true,
        ]);

        echo "✓ Created complete data for: $restaurantName\n";
    }
}
