<?php

use Illuminate\Foundation\Application;

// Suppress deprecation warnings (temporary fix for PHP 8.5+ compatibility issues in vendor)
error_reporting(E_ALL & ~E_DEPRECATED);
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'localeSessionRedirect' => \Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect::class,
            'localizationRedirect' => \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter::class,
            'localeViewPath' => \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath::class,
            'restaurant.context' => \App\Http\Middleware\CheckRestaurantContext::class,
        ]);

        // Remove the tenant middleware injection
        // $middleware->prependToGroup('web', \App\Http\Middleware\InitializeTenancyByDomainOrFail::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
