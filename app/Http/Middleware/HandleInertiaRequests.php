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
                'success' => $request->session()->get('success'),
            ],
            // Share user's restaurants for restaurant switcher (regular users)
            'user_restaurants' => $request->user() && !$request->user()->is_super_admin ? \App\Models\Restaurant::whereExists(function ($query) use ($request) {
                $query->select(\DB::raw(1))
                    ->from('restaurant_user')
                    ->whereColumn('restaurant_user.restaurant_id', 'restaurants.id')
                    ->where('restaurant_user.email', $request->user()->email);
            })->select(['id', 'name'])->get() : [],
            'active_restaurant_id' => $request->session()->get('active_restaurant_id'),

            // Share ALL restaurants for super admin
            'all_restaurants' => $request->user() && $request->user()->is_super_admin
                ? \App\Models\Restaurant::select(['id', 'name'])->get()
                : [],
            'current_restaurant' => function () use ($request) {
                if ($request->user() && $request->user()->is_super_admin) {
                    $restaurantId = session('active_restaurant_id');
                    if ($restaurantId) {
                        return \App\Models\Restaurant::find($restaurantId);
                    }
                }
                return null;
            },

            'ziggy' => fn() => [
                ...(new \Tighten\Ziggy\Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ]);
    }
}

