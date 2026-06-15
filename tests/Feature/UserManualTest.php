<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\RestaurantSubscription;
use App\Models\Plan;
use Tests\TestCase;

class UserManualTest extends TestCase
{
    public function createApplication()
    {
        putenv('ROUTING_LOCALE=en');
        return parent::createApplication();
    }

    protected User $user;
    protected User $superAdmin;
    protected Restaurant $restaurant;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Cache::flush();

        // Create active plan and restaurant context
        $plan = Plan::firstOrCreate(
            ['slug' => 'pro'],
            [
                'name' => 'Pro',
                'price_monthly' => 200.00,
                'price_yearly' => 2000.00,
                'currency' => 'AED',
                'is_active' => true,
                'enabled_features' => ['pos', 'inventory', 'loyalty'],
            ]
        );

        $this->restaurant = Restaurant::firstOrCreate(
            ['email' => 'manual_test@example.com'],
            [
                'name' => 'Manual Test Restaurant',
                'currency' => 'AED',
            ]
        );

        // Ensure active subscription
        RestaurantSubscription::firstOrCreate(
            ['restaurant_id' => $this->restaurant->id],
            [
                'plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => now(),
            ]
        );

        // Seed roles and permissions
        $role = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view_user_manual', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'manage_user_manual', 'guard_name' => 'web']);

        // Regular user (manager)
        $this->user = User::firstOrCreate(
            ['email' => 'manager_test@example.com'],
            [
                'name' => 'Manager User',
                'password' => bcrypt('password'),
            ]
        );

        // Link user to restaurant in pivot
        \Illuminate\Support\Facades\DB::connection('mongodb')
            ->table('restaurant_user')
            ->updateOrInsert(
                ['email' => $this->user->email, 'restaurant_id' => (string) $this->restaurant->id],
                ['role' => 'manager']
            );

        // Super Admin User
        $this->superAdmin = User::firstOrCreate(
            ['email' => 'superadmin_test@example.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'is_super_admin' => true,
            ]
        );

        // Active restaurant session
        session(['active_restaurant_id' => (string) $this->restaurant->id]);
    }

    protected function tearDown(): void
    {
        User::whereIn('email', ['manager_test@example.com', 'superadmin_test@example.com'])->forceDelete();
        Restaurant::where('email', 'manual_test@example.com')->forceDelete();
        \Illuminate\Support\Facades\DB::connection('mongodb')
            ->table('restaurant_user')
            ->whereIn('email', ['manager_test@example.com', 'superadmin_test@example.com'])
            ->delete();

        \App\Models\SystemConfiguration::where('key', 'user_manual_content')->delete();

        parent::tearDown();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.user-manual.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_unauthorized_user_cannot_view_user_manual(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['active_restaurant_id' => (string) $this->restaurant->id])
            ->get(route('admin.user-manual.index'));

        $response->assertStatus(403);
    }

    public function test_super_admin_can_view_user_manual_without_explicit_role_permissions(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->withSession(['active_restaurant_id' => (string) $this->restaurant->id])
            ->get(route('admin.user-manual.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('UserManual/Index')
            ->has('manualContent')
        );
    }

    public function test_unauthorized_user_cannot_update_user_manual(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['active_restaurant_id' => (string) $this->restaurant->id])
            ->post(route('admin.user-manual.update'), [
                'sections' => [],
                'faqs' => [],
            ]);

        $response->assertStatus(403);
    }

    public function test_super_admin_can_update_user_manual(): void
    {
        $testData = [
            'sections' => [
                [
                    'id' => 'pos',
                    'title' => [
                        'en' => 'Test Section English',
                        'ar' => 'Test Section Arabic',
                    ],
                    'description' => [
                        'en' => 'Test Description English',
                        'ar' => 'Test Description Arabic',
                    ],
                    'steps' => [
                        [
                            'en' => 'Test Step English',
                            'ar' => 'Test Step Arabic',
                        ]
                    ]
                ]
            ],
            'faqs' => [
                [
                    'question' => [
                        'en' => 'Test Question English',
                        'ar' => 'Test Question Arabic',
                    ],
                    'answer' => [
                        'en' => 'Test Answer English',
                        'ar' => 'Test Answer Arabic',
                    ],
                ]
            ]
        ];

        $response = $this->actingAs($this->superAdmin)
            ->withSession(['active_restaurant_id' => (string) $this->restaurant->id])
            ->post(route('admin.user-manual.update'), $testData);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        // Verify it was saved to DB configuration
        $saved = \App\Models\SystemConfiguration::get('user_manual_content');
        $this->assertEquals('Test Section English', $saved['sections'][0]['title']['en']);
    }
}
