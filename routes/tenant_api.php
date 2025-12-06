<?php

use Illuminate\Support\Facades\Route;

// Tenant API Routes
Route::group([
    'middleware' => [
        'web',
        'auth',
        \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
    ],
    'prefix' => 'app-api',
    'as' => 'app-api.',
], function () {
    Route::put('/kitchen/{order}/status', [\App\Http\Controllers\Tenant\KitchenController::class, 'updateStatus'])->name('kitchen.status.update');
});
