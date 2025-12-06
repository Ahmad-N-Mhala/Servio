<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $locale = app()->getLocale();
        $supportedLocales = config('laravellocalization.supportedLocales', []);
        $isRtl = $supportedLocales[$locale]['rtl'] ?? false;

        return array_merge(parent::share($request), [
            'locale' => $locale,
            'isRtl' => $isRtl,
            'supportedLocales' => array_keys($supportedLocales),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                ] : null,
            ],
            'flash' => [
                'message' => $request->session()->get('message'),
                'error' => $request->session()->get('error'),
            ],
            'ziggy' => fn () => [
                ...(new \Tighten\Ziggy\Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ]);
    }
}

