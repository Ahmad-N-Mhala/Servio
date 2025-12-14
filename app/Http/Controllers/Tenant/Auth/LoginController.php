<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

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

            // 1. Check for Super Admin
            if ($user->is_super_admin) {
                return redirect()->route('admin.dashboard');
            }

            // Fetch available restaurants for this user
            $restaurants = \App\Models\Restaurant::whereExists(function ($query) use ($user) {
                $query->select(\DB::raw(1))
                    ->from('restaurant_user')
                    ->whereColumn('restaurant_user.restaurant_id', 'restaurants.id')
                    ->where('restaurant_user.email', $user->email);
            })->get();

            // If user has 0 restaurants, redirect to onboarding or show error
            if ($restaurants->isEmpty()) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'No restaurant access found for this account.',
                ])->onlyInput('email');
            }

            // Auto-select the first restaurant and go to dashboard
            // Users can switch restaurants using the header dropdown
            $restaurant = $restaurants->first();
            session(['active_restaurant_id' => $restaurant->id]);
            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'Wrong credentials provided.',
        ])->onlyInput('email');
    }

    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
