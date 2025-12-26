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
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        $middleware->alias([
            'localeSessionRedirect' => \Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect::class,
            'localizationRedirect' => \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter::class,
            'localeViewPath' => \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath::class,
            'restaurant.context' => \App\Http\Middleware\CheckRestaurantContext::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        // Remove the tenant middleware injection
        // $middleware->prependToGroup('web', \App\Http\Middleware\InitializeTenancyByDomainOrFail::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Spatie\Permission\Exceptions\UnauthorizedException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'You do not have permission to view this page.'], 403);
            }

            $user = $request->user();
            if ($user) {
                return \Inertia\Inertia::render('Error/Forbidden', [
                    'message' => 'You do not have permission to view this page.',
                    'landing_url' => $user->getLandingRoute(),
                ])->toResponse($request)->setStatusCode(403);
            }

            return redirect()->route('login')
                ->with('error', 'You must be logged in to access this page.');
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'You do not have permission to view this page.'], 403);
            }

            $user = $request->user();
            if ($user) {
                return \Inertia\Inertia::render('Error/Forbidden', [
                    'message' => 'You do not have permission to view this page.',
                    'landing_url' => $user->getLandingRoute(),
                ])->toResponse($request)->setStatusCode(403);
            }

            return redirect()->route('login')
                ->with('error', 'You must be logged in to access this page.');
        });
    })->create();
