<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\OnboardingController;
use App\Http\Controllers\Tenant\DashboardController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

// Redirect /login to default locale login
Route::get('/login', function () {
    return redirect('servio/login');
});

// Also Redirect root to Servio Landing
Route::get('/', function () {
    return redirect('servio');
});

require base_path('routes/tenant_api.php');

// Main App Routes (Authenticated & Localized)
foreach (LaravelLocalization::getSupportedLocales() as $localeCode => $properties) {
    Route::group([
        'prefix' => $localeCode . '/servio',
        'middleware' => [
            'web',
            'localeSessionRedirect',
            'localizationRedirect',
            'localeViewPath',
        ],
    ], function () {
        require base_path('routes/web_localized.php');
    });
}




