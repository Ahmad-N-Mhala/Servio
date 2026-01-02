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

    // QR Code Ordering (Public Routes)
    Route::prefix('qr')->name('qr.')->group(function () {
        Route::get('/menu/{token}', [\App\Http\Controllers\Tenant\QrOrderController::class, 'showMenu'])->name('menu');
        Route::post('/order/{token}', [\App\Http\Controllers\Tenant\QrOrderController::class, 'placeOrder'])->name('order');
        Route::get('/order/{token}/{orderNumber}', [\App\Http\Controllers\Tenant\QrOrderController::class, 'getOrderStatus'])->name('order.status');
    });

    // Authenticated Routes
    Route::middleware(['auth'])->group(function () {
        // Profile Routes (Tenant/User)
        Route::get('/profile', [\App\Http\Controllers\Tenant\ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [\App\Http\Controllers\Tenant\ProfileController::class, 'update'])->name('profile.update');
        Route::put('/password', [\App\Http\Controllers\Tenant\ProfileController::class, 'updatePassword'])->name('password.update');

        // Restaurant Selection (No Context Check Required)
        Route::get('/select-restaurant', [\App\Http\Controllers\MultiRestaurantController::class, 'index'])->name('restaurants.index');
        Route::get('/select-restaurant/create', [\App\Http\Controllers\MultiRestaurantController::class, 'create'])->name('restaurants.create');
        Route::post('restaurants', [\App\Http\Controllers\MultiRestaurantController::class, 'store'])->name('restaurants.store');

        // Add Edit Routes
        Route::get('restaurants/{restaurant}/edit', [\App\Http\Controllers\MultiRestaurantController::class, 'edit'])->name('restaurants.edit');
        Route::put('restaurants/{restaurant}', [\App\Http\Controllers\MultiRestaurantController::class, 'update'])->name('restaurants.update');

        Route::post('restaurants/{restaurant}/switch', [\App\Http\Controllers\MultiRestaurantController::class, 'switch'])->name('restaurants.switch');

        // Protected by Restaurant Context
        Route::middleware(['restaurant.context'])->group(function () {
            // Dashboard
            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')
                ->middleware('permission:view_dashboard');
            Route::get('/dashboard/details', [DashboardController::class, 'getDetails'])->name('dashboard.details')
                ->middleware('permission:view_analytics');
            Route::get('/dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export')
                ->middleware('permission:export_reports');

            // Menu Management
            Route::prefix('menu')->name('menu.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Tenant\MenuController::class, 'index'])->name('index')
                    ->middleware('permission:view_menu');
                Route::post('/categories', [\App\Http\Controllers\Tenant\MenuController::class, 'storeCategory'])->name('categories.store')
                    ->middleware('permission:create_category');
                Route::put('/categories/{category}', [\App\Http\Controllers\Tenant\MenuController::class, 'updateCategory'])->name('categories.update')
                    ->middleware('permission:edit_category');
                Route::delete('/categories/{category}', [\App\Http\Controllers\Tenant\MenuController::class, 'destroyCategory'])->name('categories.destroy')
                    ->middleware('permission:delete_category');
                Route::post('/items', [\App\Http\Controllers\Tenant\MenuController::class, 'storeItem'])->name('items.store')
                    ->middleware('permission:create_item');
                Route::put('/items/{item}', [\App\Http\Controllers\Tenant\MenuController::class, 'updateItem'])->name('items.update')
                    ->middleware('permission:edit_item');
                Route::delete('/items/{item}', [\App\Http\Controllers\Tenant\MenuController::class, 'destroyItem'])->name('items.destroy')
                    ->middleware('permission:delete_item');
            });

            // Order Management
            Route::prefix('orders')->name('orders.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Tenant\OrderController::class, 'index'])->name('index')
                    ->middleware('permission:view_orders');
                Route::get('/export', [\App\Http\Controllers\Tenant\OrderController::class, 'export'])->name('export')
                    ->middleware('permission:export_reports'); // Assuming export falls under reports or orders
                Route::get('/create', [\App\Http\Controllers\Tenant\OrderController::class, 'create'])->name('create')
                    ->middleware('permission:create_order');
                Route::post('/', [\App\Http\Controllers\Tenant\OrderController::class, 'store'])->name('store')
                    ->middleware('permission:create_order');
                Route::put('/{order}/status', [\App\Http\Controllers\Tenant\OrderController::class, 'updateStatus'])->name('status.update')
                    ->middleware('permission:edit_order');
                Route::get('/{order}/bill', [\App\Http\Controllers\Tenant\OrderController::class, 'generateBill'])->name('bill')
                    ->middleware('permission:print_bill');
                Route::get('/{order}/receipt', [\App\Http\Controllers\Tenant\OrderController::class, 'receipt'])->name('receipt')
                    ->middleware('permission:print_bill');

                // Status Screen
                // Status Screen (Public View)
                Route::get('/status/screen', [\App\Http\Controllers\Tenant\OrderStatusScreenController::class, 'index'])->name('status-screen')
                    ->middleware('permission:view_order_status_screen');
                Route::get('/status/screen/poll', [\App\Http\Controllers\Tenant\OrderStatusScreenController::class, 'poll'])->name('status-screen.poll')
                    ->middleware('permission:view_order_status_screen');

                // Status Manager (Editable View for Kitchen/Counter)
                Route::get('/status/manage', [\App\Http\Controllers\Tenant\OrderStatusScreenController::class, 'manage'])->name('status-screen.manage')
                    ->middleware('permission:manage_order_status_screen');
                Route::post('/status/update-state', [\App\Http\Controllers\Tenant\OrderStatusScreenController::class, 'updateState'])->name('status-screen.update-state')
                    ->middleware('permission:update_item_status');
            });

            // Customers
            Route::resource('customers', \App\Http\Controllers\Tenant\CustomerController::class)
                ->only(['index', 'show'])
                ->middleware('permission:view_customers');

            // Tables
            Route::resource('tables', \App\Http\Controllers\Tenant\TableController::class)
                ->middleware('permission:view_tables'); // Basic view, controller should handle create/edit checks or we add here
            Route::get('tables/{table}/qr-code', [\App\Http\Controllers\Tenant\TableController::class, 'downloadQrCode'])->name('tables.qr-code')
                ->middleware('permission:view_tables');
            Route::post('tables/{table}/regenerate-qr', [\App\Http\Controllers\Tenant\TableController::class, 'regenerateQrCode'])->name('tables.regenerate-qr')
                ->middleware('permission:edit_table');

            // Loyalty
            Route::prefix('loyalty')->name('loyalty.')->group(function () {
                Route::resource('loyalty', \App\Http\Controllers\Tenant\LoyaltyController::class)
                    ->middleware('permission:view_loyalty');
                Route::get('/', [\App\Http\Controllers\Tenant\LoyaltyController::class, 'index'])->name('index')
                    ->middleware('permission:view_loyalty');
                Route::get('/customers/{customer}', [\App\Http\Controllers\Tenant\LoyaltyController::class, 'showCustomer'])->name('customers.show')
                    ->middleware('permission:view_loyalty');
                Route::post('/rewards', [\App\Http\Controllers\Tenant\LoyaltyController::class, 'storeReward'])->name('rewards.store')
                    ->middleware('permission:manage_rewards');
                Route::put('/rewards/{reward}', [\App\Http\Controllers\Tenant\LoyaltyController::class, 'updateReward'])->name('rewards.update')
                    ->middleware('permission:manage_rewards');
                Route::delete('/rewards/{reward}', [\App\Http\Controllers\Tenant\LoyaltyController::class, 'deleteReward'])->name('rewards.delete')
                    ->middleware('permission:manage_rewards');
                Route::post('/customers/{customer}/adjust-points', [\App\Http\Controllers\Tenant\LoyaltyController::class, 'adjustPoints'])->name('customers.adjust-points')
                    ->middleware('permission:adjust_points');
                Route::post('/settings', [\App\Http\Controllers\Tenant\LoyaltyController::class, 'updateSettings'])->name('settings.update')
                    ->middleware('permission:manage_rewards');
                Route::post('/rewards/{reward}/design', [\App\Http\Controllers\Tenant\LoyaltyController::class, 'updateRewardDesign'])->name('rewards.update-design')
                    ->middleware('permission:manage_rewards');

                Route::resource('earning-methods', \App\Http\Controllers\Tenant\EarningMethodController::class)
                    ->except(['create', 'edit', 'show'])
                    ->middleware('permission:manage_earning_rules');
            });

            // Staff
            Route::prefix('staff')->name('staff.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Tenant\StaffController::class, 'index'])->name('index')
                    ->middleware('permission:view_staff');
                Route::post('/', [\App\Http\Controllers\Tenant\StaffController::class, 'store'])->name('store')
                    ->middleware('permission:create_staff');
                Route::put('/{staff}', [\App\Http\Controllers\Tenant\StaffController::class, 'update'])->name('update')
                    ->middleware('permission:edit_staff');
                Route::delete('/{staff}', [\App\Http\Controllers\Tenant\StaffController::class, 'destroy'])->name('destroy')
                    ->middleware('permission:delete_staff');
            });

            // Kitchen
            Route::prefix('kitchen')->name('kitchen.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Tenant\KitchenController::class, 'index'])->name('index')
                    ->middleware('permission:view_kitchen');
                Route::put('/{order}/status', [\App\Http\Controllers\Tenant\KitchenController::class, 'updateStatus'])->name('status.update')
                    ->middleware('permission:update_item_status');
            });

            // POS
            Route::prefix('pos')->name('pos.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Tenant\POSController::class, 'index'])->name('index')
                    ->middleware('permission:view_pos');
                Route::post('/{order}/settle', [\App\Http\Controllers\Tenant\POSController::class, 'settle'])->name('settle')
                    ->middleware('permission:pos_system');
                Route::put('/{order}', [\App\Http\Controllers\Tenant\POSController::class, 'update'])->name('update')
                    ->middleware('permission:pos_system');
            });

            // Waiter Service
            Route::prefix('service')->name('service.')->group(function () {
                Route::get('/delivery', [\App\Http\Controllers\Tenant\OrderDeliveryController::class, 'index'])->name('delivery')
                    ->middleware('permission:deliver_orders');
                Route::get('/delivery/check', [\App\Http\Controllers\Tenant\OrderDeliveryController::class, 'checkNewOrders'])->name('delivery.check')
                    ->middleware('permission:deliver_orders');
                Route::post('/delivery/{order}/serve', [\App\Http\Controllers\Tenant\OrderDeliveryController::class, 'markAsServed'])->name('serve')
                    ->middleware('permission:deliver_orders');
            });

            // Cash Register
            Route::prefix('cash-register')->name('cash-register.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Tenant\CashRegisterController::class, 'index'])->name('index')
                    ->middleware('permission:view_pos');
                Route::get('/history', [\App\Http\Controllers\Tenant\CashRegisterController::class, 'history'])->name('history')
                    ->middleware('permission:view_cash_register_history');
                Route::get('/{cashRegister}/export', [\App\Http\Controllers\Tenant\CashRegisterController::class, 'export'])->name('export')
                    ->middleware('permission:view_cash_register_history');
                Route::post('/open', [\App\Http\Controllers\Tenant\CashRegisterController::class, 'open'])->name('open')
                    ->middleware('permission:pos_system');
                Route::post('/{cashRegister}/close', [\App\Http\Controllers\Tenant\CashRegisterController::class, 'close'])->name('close')
                    ->middleware('permission:pos_system');
                Route::post('/{cashRegister}/withdraw', [\App\Http\Controllers\Tenant\CashRegisterController::class, 'withdraw'])->name('withdraw')
                    ->middleware('permission:pos_system');
                Route::post('/{cashRegister}/deposit', [\App\Http\Controllers\Tenant\CashRegisterController::class, 'deposit'])->name('deposit')
                    ->middleware('permission:pos_system');
                Route::post('/record-sale', [\App\Http\Controllers\Tenant\CashRegisterController::class, 'recordSale'])->name('record-sale')
                    ->middleware('permission:pos_system');
            });

            // Communication
            Route::prefix('communication')->name('communication.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Tenant\CommunicationController::class, 'index'])->name('index')
                    ->middleware('permission:view_communication');
                Route::post('/bundles/{bundle}/purchase', [\App\Http\Controllers\Tenant\CommunicationController::class, 'purchaseBundle'])->name('bundles.purchase')
                    ->middleware('permission:purchase_sms_bundles');

                Route::post('/templates', [\App\Http\Controllers\Tenant\CommunicationController::class, 'storeTemplate'])->name('templates.store')
                    ->middleware('permission:manage_templates');
                Route::put('/templates/{template}', [\App\Http\Controllers\Tenant\CommunicationController::class, 'updateTemplate'])->name('templates.update')
                    ->middleware('permission:manage_templates');
                Route::delete('/templates/{template}', [\App\Http\Controllers\Tenant\CommunicationController::class, 'destroyTemplate'])->name('templates.destroy')
                    ->middleware('permission:manage_templates');
            });

            // Integrations
            Route::prefix('integrations')->name('integrations.')->group(function () {
                Route::get('/delivery', [\App\Http\Controllers\Tenant\DeliveryIntegrationController::class, 'index'])->name('delivery.index')
                    ->middleware('permission:view_delivery_settings');
                Route::post('/delivery', [\App\Http\Controllers\Tenant\DeliveryIntegrationController::class, 'update'])->name('delivery.update')
                    ->middleware('permission:toggle_providers');
                Route::post('/delivery/{provider}/push-menu', [\App\Http\Controllers\Tenant\DeliveryIntegrationController::class, 'pushMenu'])->name('delivery.push-menu')
                    ->middleware('permission:toggle_providers');
                Route::delete('/delivery/{provider}', [\App\Http\Controllers\Tenant\DeliveryIntegrationController::class, 'destroy'])->name('delivery.destroy')
                    ->middleware('permission:toggle_providers');
            });

            // Waste & Inventory
            Route::post('waste/{waste}/restore', [\App\Http\Controllers\Tenant\WasteController::class, 'restore'])->name('waste.restore')
                ->middleware('permission:record_waste');
            Route::resource('waste', \App\Http\Controllers\Tenant\WasteController::class)
                ->only(['index', 'store', 'update', 'destroy'])
                ->middleware('permission:view_waste'); // Store/Update/Destroy should ideally have record_waste

            Route::get('inventory/export', [\App\Http\Controllers\Tenant\InventoryController::class, 'export'])->name('inventory.export')
                ->middleware('permission:view_inventory');
            Route::get('inventory/{inventory}/history', [\App\Http\Controllers\Tenant\InventoryController::class, 'history'])->name('inventory.history')
                ->middleware('permission:view_inventory');
            Route::resource('inventory', \App\Http\Controllers\Tenant\InventoryController::class)
                ->except(['show'])
                ->middleware('permission:view_inventory'); // Add/Deduct stock handled in controller or specific routes?

            // Monthly Expenses
            Route::resource('monthly-expenses', \App\Http\Controllers\Tenant\MonthlyExpenseController::class)
                ->except(['show', 'create', 'edit'])
                ->middleware('permission:view_expense_reports');

            // Financial Overview
            Route::get('financial', [\App\Http\Controllers\Tenant\FinancialController::class, 'index'])->name('financial.index')
                ->middleware('permission:view_sales_reports');

            Route::prefix('reports')->name('reports.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Tenant\ReportController::class, 'index'])->name('sales')
                    ->middleware('permission:view_sales_reports');
                Route::get('/export', [\App\Http\Controllers\Tenant\ReportController::class, 'export'])->name('export')
                    ->middleware('permission:view_sales_reports');
            });

            // Plans & Subscription (Manage Billing)
            Route::prefix('plans')->name('plans.')->group(function () {
                Route::get('/', [\App\Http\Controllers\PlanController::class, 'index'])->name('index')
                    ->middleware('permission:manage_billing');
                Route::post('/{plan}/subscribe', [\App\Http\Controllers\PlanController::class, 'subscribe'])->name('subscribe')
                    ->middleware('permission:manage_billing');
            });

            // Receipt Template Settings
            Route::prefix('settings')->name('settings.')->group(function () {
                Route::get('/receipt-template', [\App\Http\Controllers\Tenant\ReceiptTemplateController::class, 'index'])->name('receipt-template.index')
                    ->middleware('permission:customize_receipt_template');
                Route::post('/receipt-template', [\App\Http\Controllers\Tenant\ReceiptTemplateController::class, 'store'])->name('receipt-template.store')
                    ->middleware('permission:customize_receipt_template');
            });
        });

        // Admin Portal Routes
        Route::middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->prefix('admin')->name('admin.')->group(function () {
            Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

            Route::resource('restaurants', \App\Http\Controllers\Admin\RestaurantController::class);
            Route::post('restaurants/{id}/restore', [\App\Http\Controllers\Admin\RestaurantController::class, 'restore'])->name('restaurants.restore');
            Route::resource('plans', \App\Http\Controllers\Admin\PlanController::class);
            Route::resource('integrations', \App\Http\Controllers\Admin\IntegrationController::class);
            Route::resource('subscriptions', \App\Http\Controllers\Admin\SubscriptionController::class);
            Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->only(['index']);
            Route::post('users/{id}/restore', [\App\Http\Controllers\Admin\DeletedDataController::class, 'restoreUser'])->name('users.restore');

            // Deleted Data
            Route::get('deleted-data', [\App\Http\Controllers\Admin\DeletedDataController::class, 'index'])->name('deleted-data.index');

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
            Route::post('roles', [\App\Http\Controllers\Admin\PermissionController::class, 'storeRole'])->name('roles.store');
            Route::put('roles/{role}', [\App\Http\Controllers\Admin\PermissionController::class, 'updateRole'])->name('roles.update');
            Route::delete('roles/{role}', [\App\Http\Controllers\Admin\PermissionController::class, 'destroyRole'])->name('roles.destroy');

            // Profile Routes
            Route::get('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('profile.edit');
            Route::patch('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');
            // Localization
            Route::get('localization', [\App\Http\Controllers\Admin\LocalizationController::class, 'index'])->name('localization.index');
            Route::post('localization', [\App\Http\Controllers\Admin\LocalizationController::class, 'update'])->name('localization.update');

            // System Communication (Email & SMS)
            Route::get('email-templates', [\App\Http\Controllers\Admin\CommunicationController::class, 'indexEmail'])->name('email.index');
            Route::get('sms-templates', [\App\Http\Controllers\Admin\CommunicationController::class, 'indexSms'])->name('sms.index');
            Route::post('communication-templates', [\App\Http\Controllers\Admin\CommunicationController::class, 'store'])->name('communication.store');
            Route::put('communication-templates/{template}', [\App\Http\Controllers\Admin\CommunicationController::class, 'update'])->name('communication.update');
            Route::delete('communication-templates/{template}', [\App\Http\Controllers\Admin\CommunicationController::class, 'destroy'])->name('communication.destroy');


        });
    });



});

