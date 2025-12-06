<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group([
    'middleware' => [
        'api',
        \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
        \App\Http\Middleware\InitializeTenancyByDomainOrFail::class,
    ],
], function () {
    Route::get('/menu', [\App\Http\Controllers\Tenant\PublicMenuController::class, 'show'])->name('api.menu');
    Route::get('/menu/{locale}', [\App\Http\Controllers\Tenant\PublicMenuController::class, 'show'])->name('api.menu.locale');
    
    Route::prefix('loyalty')->name('api.loyalty.')->group(function () {
        Route::post('/check-points', [\App\Http\Controllers\Tenant\PublicLoyaltyController::class, 'checkPoints'])->name('check-points');
        Route::get('/rewards', [\App\Http\Controllers\Tenant\PublicLoyaltyController::class, 'getRewards'])->name('rewards');
        Route::post('/redeem', [\App\Http\Controllers\Tenant\PublicLoyaltyController::class, 'redeemReward'])->name('redeem');
        Route::post('/history', [\App\Http\Controllers\Tenant\PublicLoyaltyController::class, 'getHistory'])->name('history');
    });
});

