<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\RestaurantSubscription;
use App\Models\Role;
use App\Models\User;
use Tests\TestCase;

class PosOrderTest extends TestCase
{
    protected $user;

    protected $restaurant;

    protected $plan;

    protected $subscription;

    protected $category;

    protected $menuItem;

    protected $ingredient;

    protected $batch;

    protected function setUp(): void
    {
        parent::setUp();

        // Truncate tables to ensure a clean slate
        Ingredient::truncate();
        IngredientBatch::truncate();
        MenuCategory::truncate();
        MenuItem::truncate();
        \Illuminate\Support\Facades\DB::table('menu_item_ingredients')->truncate();
        Order::truncate();
        User::truncate();
        Restaurant::truncate();
        Plan::truncate();
        RestaurantSubscription::truncate();

        // 2. Create Plan
        $this->plan = Plan::create([
            'slug' => 'enterprise',
            'name' => 'Enterprise',
            'price_monthly' => 400.00,
            'price_yearly' => 4500.00,
            'currency' => 'AED',
            'is_active' => true,
            'features' => ['pos', 'inventory', 'loyalty'],
            'enabled_features' => ['pos', 'inventory', 'loyalty'],
        ]);

        // 3. Create User
        $this->user = User::create([
            'name' => 'POS Test Owner',
            'email' => 'pos_test_owner@example.com',
            'phone' => '+971500000001',
            'password' => bcrypt('password123'),
            'password_set_at' => now(),
            'email_verified_at' => now(),
            'is_super_admin' => true,
        ]);

        // 4. Ensure roles exist and assign
        $ownerRole = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $this->user->assignRole('owner');

        // 5. Create Restaurant
        $this->restaurant = Restaurant::create([
            'name' => 'POS Test Restaurant',
            'slug' => 'pos-test-restaurant',
            'currency' => 'AED',
            'locale' => 'en',
            'country' => 'United Arab Emirates',
            'state' => 'Dubai',
            'city' => 'Dubai',
            'address' => 'Sheikh Zayed Road',
            'email' => 'pos_test_owner@example.com',
            'phone' => '+971500000001',
            'service_type' => 'both',
        ]);

        // 6. Link user to restaurant
        \Illuminate\Support\Facades\DB::table('restaurant_user')->insert([
            'restaurant_id' => $this->restaurant->id,
            'email' => $this->user->email,
            'role' => 'owner',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 7. Create Restaurant Subscription
        $this->subscription = RestaurantSubscription::create([
            'restaurant_id' => $this->restaurant->id,
            'plan_id' => $this->plan->id,
            'status' => 'active',
            'billing_cycle' => 'monthly',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        // 8. Put active restaurant in session for context
        session(['active_restaurant_id' => $this->restaurant->id]);
    }

    protected function tearDown(): void
    {
        // Truncate tables to clean up
        Ingredient::truncate();
        IngredientBatch::truncate();
        MenuCategory::truncate();
        MenuItem::truncate();
        \Illuminate\Support\Facades\DB::table('menu_item_ingredients')->truncate();
        Order::truncate();
        User::truncate();
        Restaurant::truncate();
        Plan::truncate();
        RestaurantSubscription::truncate();

        parent::tearDown();
    }

    public function test_pos_order_creation_validates_stock_and_deducts_via_fifo(): void
    {
        $this->actingAs($this->user);

        // 1. Create Ingredient
        $this->ingredient = Ingredient::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => ['en' => 'Beef Patty', 'ar' => 'لحم بقر'],
            'unit' => 'pcs',
            'current_stock' => 10.0,
            'reorder_level' => 2.0,
            'is_active' => true,
        ]);

        // 2. Create Ingredient Batch
        $this->batch = IngredientBatch::create([
            'ingredient_id' => $this->ingredient->id,
            'batch_number' => 'B001',
            'quantity_initial' => 10.0,
            'quantity_remaining' => 10.0,
            'cost_per_unit' => 2.50,
        ]);

        // 3. Create Menu Category
        $this->category = MenuCategory::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => ['en' => 'Burgers', 'ar' => 'برجر'],
            'is_active' => true,
        ]);

        // 4. Create Menu Item with recipe linking to Ingredient (2 patties needed per burger)
        $this->menuItem = MenuItem::create([
            'restaurant_id' => $this->restaurant->id,
            'menu_category_id' => $this->category->id,
            'name' => ['en' => 'Double Cheeseburger', 'ar' => 'دبل تشيز برجر'],
            'price' => 15.00,
            'is_available' => true,
            'recipe' => [
                [
                    'ingredient_id' => (string) $this->ingredient->id,
                    'quantity' => 2.0,
                ],
            ],
        ]);

        // Link via relationship manually for MongoDB pivot compatibility
        \Illuminate\Support\Facades\DB::table('menu_item_ingredients')->insert([
            'menu_item_id' => (string) $this->menuItem->id,
            'ingredient_id' => new \MongoDB\BSON\ObjectId((string) $this->ingredient->id),
            'quantity' => 2.0,
        ]);

        // 5. Assert that order validation fails if we order more than available stock (e.g. 6 burgers require 12 patties, we only have 10)
        $response = $this->post(route('orders.store'), [
            'type' => 'dine_in',
            'items' => [
                [
                    'menu_item_id' => (string) $this->menuItem->id,
                    'quantity' => 6,
                    'unit_price' => 15.00,
                ],
            ],
            'subtotal' => 90.00,
            'total' => 90.00,
        ]);

        $response->assertSessionHasErrors('items');

        // 6. Place a valid order for 2 cheeseburgers (requires 4 patties)
        $response2 = $this->post(route('orders.store'), [
            'type' => 'dine_in',
            'items' => [
                [
                    'menu_item_id' => (string) $this->menuItem->id,
                    'quantity' => 2,
                    'unit_price' => 15.00,
                ],
            ],
            'subtotal' => 30.00,
            'total' => 30.00,
        ]);

        $response2->assertSessionHasNoErrors();
        $response2->assertRedirect();

        // 7. Verify order is pending and stock is NOT yet deducted (stock is deducted when status moves to processing/completed)
        $order = Order::where('restaurant_id', $this->restaurant->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals('pending', $order->status);

        $this->ingredient->refresh();
        $this->assertEquals(10.0, (float) $this->ingredient->current_stock);

        // 8. Update status to 'processing'
        $response3 = $this->put(route('orders.status.update', ['order' => $order->id]), [
            'status' => 'processing',
        ]);

        $response3->assertRedirect();

        // 9. Verify order is processing and stock is deducted via FIFO (10 - 4 = 6)
        $order->refresh();
        $this->assertEquals('processing', $order->status);

        $this->ingredient->refresh();
        $this->assertEquals(6.0, (float) $this->ingredient->current_stock);

        $this->batch->refresh();
        $this->assertEquals(6.0, (float) $this->batch->quantity_remaining);
    }
}
