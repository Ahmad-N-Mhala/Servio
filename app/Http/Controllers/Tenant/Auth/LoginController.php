<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\StaffLog;

class LoginController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'The email field is required.',
            'email.email' => 'Invalid email format.',
            'password.required' => 'The password field is required.',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            $user->update(['last_login_at' => now()]);

            // 1. Check for Super Admin
            if ($user->is_super_admin) {
                return redirect()->to(\Mcamara\LaravelLocalization\Facades\LaravelLocalization::getLocalizedURL(null, route('admin.dashboard')));
            }

            // Fetch available restaurants for this user
            // FIX: Use manual query as Eloquent relationship might fail on ID types
            $restaurantIds = \Illuminate\Support\Facades\DB::connection('mongodb')
                ->table('restaurant_user')
                ->where('email', $user->email)
                ->pluck('restaurant_id')
                ->map(fn($id) => (string) $id) // Ensure strings
                ->toArray();

            $restaurants = \App\Models\Restaurant::whereIn('id', $restaurantIds)->get();

            // If user has 0 restaurants, redirect to onboarding or show error
            if ($restaurants->count() === 0) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'No restaurant access found for this account.',
                ])->onlyInput('email');
            }

            // Auto-select the first restaurant and go to dashboard
            // Users can switch restaurants using the header dropdown
            $restaurant = $restaurants->first();
            session(['active_restaurant_id' => $restaurant->id]);

            // Check Subscription Status Immediately for Login Feedback
            $subscription = $restaurant->subscription;
            $isExpired = false;

            if (!$subscription || $subscription->status !== 'active' || ($subscription->ends_at && $subscription->ends_at->isPast())) {
                $isExpired = true;
            }

            if ($isExpired) {
                Auth::logout();
                $request->session()->invalidate();

                // Return generic error or specific one
                $supportEmail = \App\Models\SystemConfiguration::get('support_email') ?? 'support@kenildock.com';
                $supportPhone = \App\Models\SystemConfiguration::get('support_phone') ?? '+9715049460976';

                return back()->withErrors([
                    'email' => __('subscription.expired_with_contact', [
                        'email' => $supportEmail,
                        'phone' => $supportPhone
                    ]),
                ])->onlyInput('email');
            }

            // Ensure the redirect URL is localized
            $targetUrl = $user->getLandingRoute();

            // Log Login Action
            $staff = $user->currentRestaurant() ? \App\Models\Staff::where('user_id', $user->id)
                ->where('restaurant_id', $user->currentRestaurant()->id)
                ->first() : null;

            if ($staff) {
                StaffLog::create([
                    'staff_id' => $staff->id,
                    'user_id' => $user->id,
                    'action' => 'Login',
                    'changes' => [],
                    'causer_id' => $user->id,
                    'causer_name' => $user->name,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            } else {
                // Even if no staff record found for current context (super admin or multi-tenant edge case), we can log with null staff_id
                StaffLog::create([
                    'staff_id' => null,
                    'user_id' => $user->id,
                    'action' => 'Login',
                    'changes' => [],
                    'causer_id' => $user->id,
                    'causer_name' => $user->name,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            }

            // getLandingRoute() now returns a fully localized URL (e.g. /en/servio/dashboard),
            // so we don't need to wrap it again.
            return redirect()->intended($targetUrl);
        }

        return back()->withErrors([
            'email' => 'Wrong credentials provided.',
        ])->onlyInput('email');
    }

    public function destroy(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            // Log Logout
            $staff = $user->currentRestaurant() ? \App\Models\Staff::where('user_id', $user->id)
                ->where('restaurant_id', $user->currentRestaurant()->id)
                ->first() : null;

            StaffLog::create([
                'staff_id' => $staff ? $staff->id : null,
                'user_id' => $user->id,
                'action' => 'Logout',
                'changes' => [],
                'causer_id' => $user->id,
                'causer_name' => $user->name,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Inertia::location(route('login'));
    }
}
