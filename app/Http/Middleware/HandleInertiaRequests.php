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
            'auth' => function () use ($request) {
                return [
                    'user' => $request->user() ? [
                        'id' => $request->user()->id,
                        'name' => $request->user()->name,
                        'email' => $request->user()->email,
                        'permissions' => $request->user()->getPermissionsForCurrentRestaurant(),
                        'role' => $request->user()->getRestaurantRole(),
                        'roles' => $request->user()->getRoleNames(),
                    ] : null,
                ];
            },
            'flash' => function () use ($request) {
                return [
                    'message' => $request->session()->get('message'),
                    'error' => $request->session()->get('error'),
                    'success' => $request->session()->get('success'),
                ];
            },
            // Share user's restaurants for restaurant switcher (regular users)
            'user_restaurants' => function () use ($request) {
                if ($request->user() && !$request->user()->is_super_admin) {
                    $ids = \Illuminate\Support\Facades\DB::table('restaurant_user')
                        ->where('email', $request->user()->email)
                        ->pluck('restaurant_id')
                        ->map(fn($id) => (string) $id)
                        ->toArray();
                    return empty($ids) ? [] : \App\Models\Restaurant::whereIn('id', $ids)->select(['id', 'name', 'logo'])->get();
                }
                return [];
            },
            'active_restaurant_id' => function () use ($request) {
                return $request->session()->get('active_restaurant_id');
            },

            // Share ALL restaurants for super admin
            'all_restaurants' => function () use ($request) {
                return $request->user() && $request->user()->is_super_admin
                    ? \App\Models\Restaurant::select(['id', 'name'])->get()
                    : [];
            },
            'current_restaurant' => function () use ($request) {
                if ($request->user()) {
                    $restaurant = $request->user()->currentRestaurant();
                    if ($restaurant) {
                        return [
                            'id' => $restaurant->id,
                            'name' => $restaurant->name,
                            'slug' => $restaurant->slug,
                            'logo' => $restaurant->logo,
                            'settings' => $restaurant->settings,
                            'receipt_template' => $restaurant->receipt_template,
                            'currency' => $restaurant->currency,
                            'google_map_location' => $restaurant->google_map_location,
                            'phone' => $restaurant->phone,
                            'email' => $restaurant->email,
                            'address' => $restaurant->address,
                        ];
                    }
                }
                return null;
            },

            // Share current subscription for the active restaurant
            'current_subscription' => function () use ($request) {
                if ($request->user()) {
                    $restaurant = $request->user()->currentRestaurant();
                    if ($restaurant) {
                        return \App\Models\RestaurantSubscription::where('restaurant_id', $restaurant->id)
                            ->where('status', 'active')
                            ->with('plan')
                            ->latest()
                            ->first();
                    }
                }
                return null;
            },

            'ziggy' => fn() => [
                ...(new \Tighten\Ziggy\Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'translations' => function () use ($locale) {
                return \Illuminate\Support\Facades\Cache::remember("translations_{$locale}", 86400, function () use ($locale) {
                    $langPath = lang_path();
                    $data = [];

                    $path = $langPath . '/' . $locale;
                    if (\Illuminate\Support\Facades\File::exists($path)) {
                        $files = \Illuminate\Support\Facades\File::files($path);
                        foreach ($files as $file) {
                            $name = $file->getFilenameWithoutExtension();
                            $content = include $file->getPathname();
                            $data[$locale][$name] = $content;
                        }
                    }
                    return $data;
                });
            },
            'system_settings' => function () {
                return \Illuminate\Support\Facades\Cache::remember('system_settings', 86400, function () {
                    return [
                        'support_email' => \App\Models\SystemConfiguration::get('support_email') ?? 'support@kenildock.com',
                        'support_phone' => \App\Models\SystemConfiguration::get('support_phone') ?? '+9715049460976',
                    ];
                });
            }
        ]);
    }
}

