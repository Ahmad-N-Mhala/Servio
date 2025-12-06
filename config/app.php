<?php

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;

return [
    'name' => env('APP_NAME', 'RestaurFy'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => 'UTC',
    'locale' => 'en',
    'fallback_locale' => 'en',
    'faker_locale' => 'en_US',
    'key' => env('APP_KEY'),
    'cipher' => 'AES-256-CBC',
    'maintenance' => [
        'driver' => 'file',
    ],
    'providers' => ServiceProvider::defaultProviders()->merge([
        App\Providers\AppServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        Mcamara\LaravelLocalization\LaravelLocalizationServiceProvider::class,
        Spatie\Permission\PermissionServiceProvider::class,
        Spatie\Translatable\TranslatableServiceProvider::class,
        Stancl\Tenancy\TenancyServiceProvider::class,
    ])->toArray(),
    'aliases' => Facade::defaultAliases()->merge([
        'LaravelLocalization' => Mcamara\LaravelLocalization\Facades\LaravelLocalization::class,
    ])->toArray(),
];

