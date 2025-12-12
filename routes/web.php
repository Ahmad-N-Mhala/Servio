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

// Central Domain Routes
Route::group([
    'prefix' => LaravelLocalization::setLocale(),
    'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath'],
], function () {
    Route::get('/', function () {
        // Central domain - redirect to onboarding
        return redirect()->route('onboard');
    });

    Route::get('/onboard', [OnboardingController::class, 'show'])->name('onboard');
    Route::post('/onboard', [OnboardingController::class, 'store'])->name('onboard.store');
    Route::get('/onboard/success', [OnboardingController::class, 'success'])->name('onboard.success');
});

// Tenant Domain Redirect (Root to /en)
Route::group([
    'middleware' => [
        'web',
        \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
        \App\Http\Middleware\InitializeTenancyByDomainOrFail::class,
    ],
], function () {
    Route::get('/', function () {
        return redirect('/en');
    });
});

// Tenant Domain Routes (Localized)
Route::group([
    'prefix' => LaravelLocalization::setLocale(),
    'middleware' => [
        'web',
        'localeSessionRedirect',
        'localizationRedirect',
        'localeViewPath',
        \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
        \App\Http\Middleware\InitializeTenancyByDomainOrFail::class,
    ],
], function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/login', [\App\Http\Controllers\Tenant\Auth\LoginController::class, 'show'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Tenant\Auth\LoginController::class, 'store'])->name('login.store');

    Route::post('/logout', [\App\Http\Controllers\Tenant\Auth\LoginController::class, 'destroy'])->name('logout');

    Route::get('/forgot-password', [\App\Http\Controllers\Tenant\Auth\PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [\App\Http\Controllers\Tenant\Auth\PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [\App\Http\Controllers\Tenant\Auth\NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [\App\Http\Controllers\Tenant\Auth\NewPasswordController::class, 'store'])->name('password.store');

    Route::middleware(['auth'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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
            Route::get('/create', [\App\Http\Controllers\Tenant\OrderController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Tenant\OrderController::class, 'store'])->name('store');
            Route::put('/{order}/status', [\App\Http\Controllers\Tenant\OrderController::class, 'updateStatus'])->name('status.update');
            Route::get('/{order}/bill', [\App\Http\Controllers\Tenant\OrderController::class, 'generateBill'])->name('bill');
        });

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
        });

        Route::prefix('pos')->name('pos.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Tenant\POSController::class, 'index'])->name('index');
            Route::post('/{order}/settle', [\App\Http\Controllers\Tenant\POSController::class, 'settle'])->name('settle');
        });
    });
});

