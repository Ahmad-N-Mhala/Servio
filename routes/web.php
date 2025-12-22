<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\OnboardingController;
use App\Http\Controllers\Tenant\DashboardController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

// Redirect /login to default locale login
Route::get('/login', function () {
    return redirect()->route('login');
});

require base_path('routes/tenant_api.php');

// Main App Routes (Authenticated & Localized)
Route::group([
    'prefix' => LaravelLocalization::setLocale(),
    'middleware' => [
        'web',
        'localeSessionRedirect',
        'localizationRedirect',
        'localeViewPath',
    ],
], function () {
    // Public Routes
    Route::get('/', function () {
        return redirect()->route('login');
    });

    Route::get('/login', [\App\Http\Controllers\Tenant\Auth\LoginController::class, 'show'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Tenant\Auth\LoginController::class, 'store'])->name('login.store');
    Route::post('/logout', [\App\Http\Controllers\Tenant\Auth\LoginController::class, 'destroy'])->name('logout');

    Route::get('/forgot-password', [\App\Http\Controllers\Tenant\Auth\PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [\App\Http\Controllers\Tenant\Auth\PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [\App\Http\Controllers\Tenant\Auth\NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [\App\Http\Controllers\Tenant\Auth\NewPasswordController::class, 'store'])->name('password.store');

    // Onboarding (optional if you still want signup flow)
    Route::get('/onboard', [OnboardingController::class, 'show'])->name('onboard');
    Route::post('/onboard', [OnboardingController::class, 'store'])->name('onboard.store');

    // Authenticated Routes
    Route::middleware(['auth'])->group(function () {
        // Profile Routes (Tenant/User)
        Route::get('/profile', [\App\Http\Controllers\Tenant\ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [\App\Http\Controllers\Tenant\ProfileController::class, 'update'])->name('profile.update');
        Route::put('/password', [\App\Http\Controllers\Tenant\ProfileController::class, 'updatePassword'])->name('password.update');

        // Restaurant Selection (No Context Check Required)
        Route::get('/select-restaurant', [\App\Http\Controllers\MultiRestaurantController::class, 'index'])->name('restaurants.index');
        Route::get('/select-restaurant/create', [\App\Http\Controllers\MultiRestaurantController::class, 'create'])->name('restaurants.create');
        Route::post('/select-restaurant/create', [\App\Http\Controllers\MultiRestaurantController::class, 'store'])->name('restaurants.store');
        Route::post('/switch-restaurant/{restaurant}', [\App\Http\Controllers\MultiRestaurantController::class, 'switch'])->name('restaurants.switch');

        // Protected by Restaurant Context
        Route::middleware(['restaurant.context'])->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
            Route::get('/dashboard/details', [DashboardController::class, 'getDetails'])->name('dashboard.details');
            Route::get('/dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');

            Route::prefix('menu')->name('menu.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Tenant\MenuController::class, 'index'])->name('index');
                Route::post('/categories', [\App\Http\Controllers\Tenant\MenuController::class, 'storeCategory'])->name('categories.store');
                Route::put('/categories/{category}', [\App\Http\Controllers\Tenant\MenuController::class, 'updateCategory'])->name('categories.update');
                Route::delete('/categories/{category}', [\App\Http\Controllers\Tenant\MenuController::class, 'destroyCategory'])->name('categories.destroy');
                Route::post('/items', [\App\Http\Controllers\Tenant\MenuController::class, 'storeItem'])->name('items.store');
                Route::put('/items/{item}', [\App\Http\Controllers\Tenant\MenuController::class, 'updateItem'])->name('items.update');
                Route::delete('/items/{item}', [\App\Http\Controllers\Tenant\MenuController::class, 'destroyItem'])->name('items.destroy');
            });

            Route::prefix('orders')->name('orders.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Tenant\OrderController::class, 'index'])->name('index');
                Route::get('/export', [\App\Http\Controllers\Tenant\OrderController::class, 'export'])->name('export');
                Route::get('/create', [\App\Http\Controllers\Tenant\OrderController::class, 'create'])->name('create');
                Route::post('/', [\App\Http\Controllers\Tenant\OrderController::class, 'store'])->name('store');
                Route::put('/{order}/status', [\App\Http\Controllers\Tenant\OrderController::class, 'updateStatus'])->name('status.update');
                Route::get('/{order}/bill', [\App\Http\Controllers\Tenant\OrderController::class, 'generateBill'])->name('bill');
            });

            Route::resource('customers', \App\Http\Controllers\Tenant\CustomerController::class)->only(['index', 'show']);

            Route::resource('tables', \App\Http\Controllers\Tenant\TableController::class);

            Route::prefix('loyalty')->name('loyalty.')->group(function () {
                Route::resource('loyalty', \App\Http\Controllers\Tenant\LoyaltyController::class);
                Route::get('/', [\App\Http\Controllers\Tenant\LoyaltyController::class, 'index'])->name('index');
                Route::get('/customers/{customer}', [\App\Http\Controllers\Tenant\LoyaltyController::class, 'showCustomer'])->name('customers.show');
                Route::post('/rewards', [\App\Http\Controllers\Tenant\LoyaltyController::class, 'storeReward'])->name('rewards.store');
                Route::put('/rewards/{reward}', [\App\Http\Controllers\Tenant\LoyaltyController::class, 'updateReward'])->name('rewards.update');
                Route::delete('/rewards/{reward}', [\App\Http\Controllers\Tenant\LoyaltyController::class, 'deleteReward'])->name('rewards.delete');
                Route::post('/customers/{customer}/adjust-points', [\App\Http\Controllers\Tenant\LoyaltyController::class, 'adjustPoints'])->name('customers.adjust-points');

                Route::resource('earning-methods', \App\Http\Controllers\Tenant\EarningMethodController::class)->except(['create', 'edit', 'show']);
            });

            Route::prefix('staff')->name('staff.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Tenant\StaffController::class, 'index'])->name('index');
                Route::post('/', [\App\Http\Controllers\Tenant\StaffController::class, 'store'])->name('store');
                Route::put('/{staff}', [\App\Http\Controllers\Tenant\StaffController::class, 'update'])->name('update');
                Route::delete('/{staff}', [\App\Http\Controllers\Tenant\StaffController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('kitchen')->name('kitchen.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Tenant\KitchenController::class, 'index'])->name('index');
                Route::put('/{order}/status', [\App\Http\Controllers\Tenant\KitchenController::class, 'updateStatus'])->name('status.update');
            });

            Route::prefix('pos')->name('pos.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Tenant\POSController::class, 'index'])->name('index');
                Route::post('/{order}/settle', [\App\Http\Controllers\Tenant\POSController::class, 'settle'])->name('settle');
            });

            Route::prefix('communication')->name('communication.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Tenant\CommunicationController::class, 'index'])->name('index');
                Route::post('/bundles/{bundle}/purchase', [\App\Http\Controllers\Tenant\CommunicationController::class, 'purchaseBundle'])->name('bundles.purchase');

                Route::post('/templates', [\App\Http\Controllers\Tenant\CommunicationController::class, 'storeTemplate'])->name('templates.store');
                Route::put('/templates/{template}', [\App\Http\Controllers\Tenant\CommunicationController::class, 'updateTemplate'])->name('templates.update');
                Route::delete('/templates/{template}', [\App\Http\Controllers\Tenant\CommunicationController::class, 'destroyTemplate'])->name('templates.destroy');
            });

            Route::prefix('integrations')->name('integrations.')->group(function () {
                Route::get('/delivery', [\App\Http\Controllers\Tenant\DeliveryIntegrationController::class, 'index'])->name('delivery.index');
                Route::post('/delivery', [\App\Http\Controllers\Tenant\DeliveryIntegrationController::class, 'update'])->name('delivery.update');
            });

            Route::post('waste/{waste}/restore', [\App\Http\Controllers\Tenant\WasteController::class, 'restore'])->name('waste.restore');
            Route::resource('waste', \App\Http\Controllers\Tenant\WasteController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::get('inventory/{inventory}/history', [\App\Http\Controllers\Tenant\InventoryController::class, 'history'])->name('inventory.history');
            Route::resource('inventory', \App\Http\Controllers\Tenant\InventoryController::class)->except(['show']);

            // Monthly Expenses
            Route::resource('monthly-expenses', \App\Http\Controllers\Tenant\MonthlyExpenseController::class)->except(['show', 'create', 'edit']);

            // Financial Overview (combines expenses and reports)
            Route::get('financial', [\App\Http\Controllers\Tenant\FinancialController::class, 'index'])->name('financial.index');

            Route::prefix('reports')->name('reports.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Tenant\ReportController::class, 'index'])->name('sales'); // Defaulting index to sales for now, or use specific path
            });

            // Plans & Subscription
            Route::prefix('plans')->name('plans.')->group(function () {
                Route::get('/', [\App\Http\Controllers\PlanController::class, 'index'])->name('index');
                Route::post('/{plan}/subscribe', [\App\Http\Controllers\PlanController::class, 'subscribe'])->name('subscribe');
            });
        });

        // Admin Portal Routes
        Route::middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->prefix('admin')->name('admin.')->group(function () {
            Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

            Route::resource('restaurants', \App\Http\Controllers\Admin\RestaurantController::class);
            Route::resource('plans', \App\Http\Controllers\Admin\PlanController::class);
            Route::resource('integrations', \App\Http\Controllers\Admin\IntegrationController::class);
            Route::resource('subscriptions', \App\Http\Controllers\Admin\SubscriptionController::class);

            // Delivery Providers Management
            Route::get('delivery-providers', [\App\Http\Controllers\Admin\DeliveryProviderController::class, 'index'])->name('delivery-providers.index');
            Route::get('delivery-providers/create', [\App\Http\Controllers\Admin\DeliveryProviderController::class, 'create'])->name('delivery-providers.create');
            Route::post('delivery-providers', [\App\Http\Controllers\Admin\DeliveryProviderController::class, 'store'])->name('delivery-providers.store');
            Route::get('delivery-providers/{deliveryProvider}/edit', [\App\Http\Controllers\Admin\DeliveryProviderController::class, 'edit'])->name('delivery-providers.edit');
            Route::put('delivery-providers/{deliveryProvider}', [\App\Http\Controllers\Admin\DeliveryProviderController::class, 'update'])->name('delivery-providers.update');
            Route::delete('delivery-providers/{deliveryProvider}', [\App\Http\Controllers\Admin\DeliveryProviderController::class, 'destroy'])->name('delivery-providers.destroy');
            Route::post('delivery-providers/{deliveryProvider}/toggle-status', [\App\Http\Controllers\Admin\DeliveryProviderController::class, 'toggleStatus'])->name('delivery-providers.toggle-status');

            // Permissions Management
            Route::get('permissions', [\App\Http\Controllers\Admin\PermissionController::class, 'index'])->name('permissions.index');
            Route::post('permissions', [\App\Http\Controllers\Admin\PermissionController::class, 'update'])->name('permissions.update');

            // Profile Routes
            Route::get('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('profile.edit');
            Route::patch('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');
            Route::put('/password', [\App\Http\Controllers\Admin\ProfileController::class, 'updatePassword'])->name('password.update');


        });
    });

    Route::get('/debug-perms', function () {
        $user = auth()->user();
        if (!$user)
            return 'Not logged in';

        $roles = $user->getRoleNames();
        $permissions = $user->getAllPermissions()->pluck('name');

        return [
            'user' => $user->email,
            'roles' => $roles,
            'permissions' => $permissions,
            'can_delete_inventory' => $user->can('delete_inventory'),
        ];
    });

});

