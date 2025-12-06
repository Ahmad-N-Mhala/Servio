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
        \Log::info('Login attempt received', [
            'email' => $request->email,
            'remember' => $request->boolean('remember'),
            'tenant_id' => tenancy()->tenant?->id,
        ]);

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        \Log::info('Credentials validated, attempting authentication');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            \Log::info('Authentication successful', ['user_id' => Auth::id()]);
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        \Log::warning('Authentication failed for email: ' . $request->email);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
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
