<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Restaurant;
use App\Models\Staff;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DashboardDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $restaurant = Restaurant::first();

        if (!$restaurant) {
            $this->command->error('No restaurant found. Please run tenant migrations first.');
            return;
        }

        // Create menu categories
        $categories = [
            ['name' => ['en' => 'Appetizers', 'ar' => 'المقبلات'], 'sort_order' => 1],
            ['name' => ['en' => 'Main Courses', 'ar' => 'الأطباق الرئيسية'], 'sort_order' => 2],
            ['name' => ['en' => 'Desserts', 'ar' => 'الحلويات'], 'sort_order' => 3],
            ['name' => ['en' => 'Beverages', 'ar' => 'المشروبات'], 'sort_order' => 4],
        ];

        foreach ($categories as $categoryData) {
            $existing = MenuCategory::where('restaurant_id', $restaurant->id)
                ->whereRaw("name->>'en' = ?", [$categoryData['name']['en']])
                ->first();

            if (!$existing) {
                MenuCategory::create([
                    'restaurant_id' => $restaurant->id,
                    'name' => $categoryData['name'],
                    'is_active' => true,
                    'sort_order' => $categoryData['sort_order'],
                ]);
            }
        }

        // Create menu items
        $menuItems = [
            // Appetizers
            ['category' => 'Appetizers', 'name' => ['en' => 'Hummus', 'ar' => 'حمص'], 'price' => 25.00],
            ['category' => 'Appetizers', 'name' => ['en' => 'Falafel', 'ar' => 'فلافل'], 'price' => 20.00],
            ['category' => 'Appetizers', 'name' => ['en' => 'Spring Rolls', 'ar' => 'سبرينج رول'], 'price' => 30.00],

            // Main Courses
            ['category' => 'Main Courses', 'name' => ['en' => 'Grilled Chicken', 'ar' => 'دجاج مشوي'], 'price' => 65.00],
            ['category' => 'Main Courses', 'name' => ['en' => 'Beef Burger', 'ar' => 'برجر لحم'], 'price' => 55.00],
            ['category' => 'Main Courses', 'name' => ['en' => 'Margherita Pizza', 'ar' => 'بيتزا مارجريتا'], 'price' => 50.00],
            ['category' => 'Main Courses', 'name' => ['en' => 'Pasta Carbonara', 'ar' => 'باستا كاربونارا'], 'price' => 60.00],
            ['category' => 'Main Courses', 'name' => ['en' => 'Fish & Chips', 'ar' => 'سمك وبطاطس'], 'price' => 70.00],

            // Desserts
            ['category' => 'Desserts', 'name' => ['en' => 'Chocolate Cake', 'ar' => 'كيك شوكولاتة'], 'price' => 35.00],
            ['category' => 'Desserts', 'name' => ['en' => 'Ice Cream', 'ar' => 'آيس كريم'], 'price' => 25.00],
            ['category' => 'Desserts', 'name' => ['en' => 'Tiramisu', 'ar' => 'تيراميسو'], 'price' => 40.00],

            // Beverages
            ['category' => 'Beverages', 'name' => ['en' => 'Fresh Orange Juice', 'ar' => 'عصير برتقال طازج'], 'price' => 20.00],
            ['category' => 'Beverages', 'name' => ['en' => 'Coffee', 'ar' => 'قهوة'], 'price' => 15.00],
            ['category' => 'Beverages', 'name' => ['en' => 'Soft Drink', 'ar' => 'مشروب غازي'], 'price' => 10.00],
        ];

        $createdMenuItems = [];
        foreach ($menuItems as $itemData) {
            $category = MenuCategory::where('restaurant_id', $restaurant->id)
                ->whereRaw("name->>'en' = ?", [$itemData['category']])
                ->first();

            if ($category) {
                $existing = MenuItem::where('restaurant_id', $restaurant->id)
                    ->whereRaw("name->>'en' = ?", [$itemData['name']['en']])
                    ->first();

                if (!$existing) {
                    $item = MenuItem::create([
                        'restaurant_id' => $restaurant->id,
                        'menu_category_id' => $category->id,
                        'name' => $itemData['name'],
                        'description' => 'Delicious ' . $itemData['name']['en'],
                        'price' => $itemData['price'],
                        'is_available' => true,
                    ]);
                    $createdMenuItems[] = $item;
                } else {
                    $createdMenuItems[] = $existing;
                }
            }
        }

        // Create customers
        $customerNames = [
            'Ahmed Ali',
            'Fatima Hassan',
            'Mohammed Khalid',
            'Sara Ahmed',
            'Omar Abdullah',
            'Layla Ibrahim',
            'Youssef Mahmoud',
            'Noor Saleh',
            'Khaled Rashid',
            'Maryam Yousef'
        ];

        $customers = [];
        foreach ($customerNames as $index => $name) {
            $customer = Customer::firstOrCreate(
                ['restaurant_id' => $restaurant->id, 'phone' => '+971-50-' . str_pad((string) ($index + 1), 7, '0', STR_PAD_LEFT)],
                [
                    'name' => $name,
                    'email' => strtolower(str_replace(' ', '.', $name)) . '@example.com',
                    'loyalty_tier' => ['bronze', 'silver', 'gold'][array_rand(['bronze', 'silver', 'gold'])],
                    'is_active' => true,
                ]
            );
            $customers[] = $customer;
        }

        // Create orders for the last 30 days
        $statuses = ['pending', 'completed', 'completed', 'completed', 'cancelled']; // More completed orders
        $orderNumber = 1000;

        for ($day = 30; $day >= 0; $day--) {
            $date = now()->subDays($day);

            // Create 3-8 orders per day
            $ordersPerDay = rand(3, 8);

            for ($i = 0; $i < $ordersPerDay; $i++) {
                $customer = $customers[array_rand($customers)];
                $status = $statuses[array_rand($statuses)];

                // Random hour between 11 AM and 10 PM
                $hour = rand(11, 22);
                $minute = rand(0, 59);
                $orderDate = $date->copy()->setTime($hour, $minute);

                $order = Order::create([
                    'restaurant_id' => $restaurant->id,
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'customer_phone' => $customer->phone,
                    'order_number' => 'ORD-' . str_pad((string) $orderNumber++, 6, '0', STR_PAD_LEFT),
                    'status' => $status,
                    'subtotal' => 0,
                    'tax' => 0,
                    'total' => 0,
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]);

                // Add 1-5 items to each order
                $itemCount = rand(1, 5);
                $subtotal = 0;

                for ($j = 0; $j < $itemCount; $j++) {
                    $menuItem = $createdMenuItems[array_rand($createdMenuItems)];
                    $quantity = rand(1, 3);
                    $itemTotal = $menuItem->price * $quantity;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'menu_item_id' => $menuItem->id,
                        'quantity' => $quantity,
                        'unit_price' => $menuItem->price,
                        'total_price' => $itemTotal,
                    ]);

                    $subtotal += $itemTotal;
                }

                $tax = $subtotal * 0.05; // 5% tax
                $total = $subtotal + $tax;

                $order->update([
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'total' => $total,
                ]);
            }
        }

        // Create some staff members
        $staffMembers = [
            ['name' => 'John Manager', 'role' => 'manager', 'email' => 'manager@restaurant.com'],
            ['name' => 'Alice Waiter', 'role' => 'waiter', 'email' => 'waiter1@restaurant.com'],
            ['name' => 'Bob Chef', 'role' => 'chef', 'email' => 'chef@restaurant.com'],
            ['name' => 'Carol Waiter', 'role' => 'waiter', 'email' => 'waiter2@restaurant.com'],
        ];

        foreach ($staffMembers as $staffData) {
            Staff::firstOrCreate(
                ['restaurant_id' => $restaurant->id, 'email' => $staffData['email']],
                [
                    'name' => $staffData['name'],
                    'phone' => '+971-50-' . rand(1000000, 9999999),
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('Dashboard demo data seeded successfully!');
        $this->command->info('Created:');
        $this->command->info('- ' . count($createdMenuItems) . ' menu items');
        $this->command->info('- ' . count($customers) . ' customers');
        $this->command->info('- ~' . (($orderNumber - 1000)) . ' orders over 30 days');
        $this->command->info('- 4 staff members');
    }
}
