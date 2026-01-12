<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Tests\TestCase;

class DeliveryOrderTest extends TestCase
{
    protected $restaurant;
    protected $user;
    protected $customer;
    protected $category;
    protected $menuItem;

    protected function setUp(): void
    {
        parent::setUp();
        \Mcamara\LaravelLocalization\Facades\LaravelLocalization::setLocale('en');

        // 1. Create Restaurant
        $this->restaurant = Restaurant::factory()->create();

        // 2. Create User linked to Restaurant
        $this->user = User::factory()->create([
            'restaurant_id' => $this->restaurant->id,
        ]);

        // 3. Create Role and Permission (Mongo)
        $role = Role::create(['name' => 'Manager', 'guard_name' => 'web']);
        $permission = Permission::create(['name' => 'manage_delivery_orders', 'guard_name' => 'web']);

        // Assign permission to role
        // Since custom Mongo implementation, check if givesPermissionTo works, or manual
        // usually $role->givePermissionTo($permission);
        // But let's assume standard Spatie trait usage on Mongo model works.
        $role->givePermissionTo($permission);

        // 4. Attach User to Restaurant with Role
        \Illuminate\Support\Facades\DB::connection('mongodb')
            ->table('restaurant_user')
            ->insert([
                'email' => $this->user->email,
                'restaurant_id' => (string) $this->restaurant->id,
                'role' => 'Manager'
            ]);

        // 5. Create Menu Data
        $this->category = MenuCategory::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Test Category',
            'is_active' => true,
        ]);

        $this->menuItem = MenuItem::create([
            'restaurant_id' => $this->restaurant->id,
            'menu_category_id' => $this->category->id,
            'name' => 'Test Item',
            'price' => 10.00,
            'is_available' => true,
            'is_active' => true,
        ]);

        // 6. Create Customer
        $this->customer = Customer::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Test Customer',
            'phone' => '+999999999',
        ]);
        // 7. Config Inertia for check skipping
        config(['inertia.testing.ensure_pages_exist' => false]);
    }

    protected function tearDown(): void
    {
        if ($this->restaurant) {
            Order::where('restaurant_id', $this->restaurant->id)->delete();
            MenuItem::where('restaurant_id', $this->restaurant->id)->delete();
            MenuCategory::where('restaurant_id', $this->restaurant->id)->delete();
            Customer::where('restaurant_id', $this->restaurant->id)->delete();
            User::where('restaurant_id', $this->restaurant->id)->delete();
            Permission::where('name', 'manage_delivery_orders')->delete();
            Role::where('name', 'Manager')->delete();
            $this->restaurant->delete();
        }
        parent::tearDown();
    }

    public function test_authorized_user_can_access_create_page()
    {
        $this->withoutMiddleware([
            \Spatie\Permission\Middleware\PermissionMiddleware::class,
            \App\Http\Middleware\CheckRestaurantContext::class,
            \Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect::class,
            \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter::class,
            \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath::class,
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['active_restaurant_id' => $this->restaurant->id])
            ->get('/delivery-orders/create');

        $response->assertStatus(200);
        $response->assertInertia(
            fn($page) => $page
                ->component('Orders/DeliveryCreate')
        );
    }

    public function test_store_delivery_order_with_overridden_price()
    {
        $this->withoutMiddleware([
            \Spatie\Permission\Middleware\PermissionMiddleware::class,
            \App\Http\Middleware\CheckRestaurantContext::class,
            \Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect::class,
            \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter::class,
            \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath::class,
        ]);

        // Data payload
        $payload = [
            'customer_id' => $this->customer->id,
            'type' => 'delivery',
            'delivery_provider' => 'uber-eats',
            'items' => [
                [
                    'menu_item_id' => $this->menuItem->id,
                    'quantity' => 2,
                    'unit_price' => 10.00,
                    'notes' => 'Extra spicy',
                    'extras' => []
                ]
            ],
            'subtotal' => 20.00,
            'tax' => 1.00,
            'discount_amount' => 0,
            'total' => 50.00, // User OVERRIDE
            'notes' => 'Leave at door',
        ];

        $response = $this->actingAs($this->user)
            ->withSession(['active_restaurant_id' => $this->restaurant->id])
            ->post('/delivery-orders', $payload);

        $response->assertRedirect();

        // Manual assertion
        $order = Order::where('restaurant_id', $this->restaurant->id)
            ->where('delivery_provider', 'uber-eats')
            ->latest()
            ->first();

        $this->assertNotNull($order);
        $this->assertEquals('delivery', $order->type);
        $this->assertEquals(50.00, $order->total); // Eloquent casts decimal:2 to string/float
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('online', $order->payment_method);
        $this->assertEquals($this->customer->id, $order->customer_id);
    }

    public function test_store_delivery_order_validation_fails_without_provider()
    {
        $this->withoutMiddleware([
            \Spatie\Permission\Middleware\PermissionMiddleware::class,
            \App\Http\Middleware\CheckRestaurantContext::class,
            \Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect::class,
            \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter::class,
            \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath::class,
        ]);

        $payload = [
            'customer_id' => $this->customer->id,
            'type' => 'delivery',
            // 'delivery_provider' => 'uber-eats', // MISSING
            'items' => [
                [
                    'menu_item_id' => $this->menuItem->id,
                    'quantity' => 1,
                    'unit_price' => 10.00,
                ]
            ],
            'subtotal' => 10.00,
            'tax' => 0.50,
            'total' => 10.50,
        ];

        $response = $this->actingAs($this->user)
            ->withSession(['active_restaurant_id' => $this->restaurant->id])
            ->post('/delivery-orders', $payload);

        $response->assertSessionHasErrors('delivery_provider');
    }
}
