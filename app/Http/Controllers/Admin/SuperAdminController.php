<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class SuperAdminController extends Controller
{
    /**
     * Display a listing of super admins.
     */
    public function index()
    {
        $superAdmins = User::where('is_super_admin', true)
            ->latest()
            ->get();

        return Inertia::render('Admin/SuperAdmins/Index', [
            'superAdmins' => $superAdmins
        ]);
    }

    /**
     * Store a newly created super admin in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_super_admin' => true,
        ]);

        return redirect()->back()->with('success', 'Super Admin created successfully.');
    }

    /**
     * Remove the specified super admin from storage.
     */
    public function destroy(User $superAdmin)
    {
        if ($superAdmin->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot delete yourself.');
        }

        if (!$superAdmin->is_super_admin) {
            return redirect()->back()->with('error', 'User is not a super admin.');
        }

        // Hard Delete or toggle? Usually Hard Delete for purely admin users, 
        // but if they might own test restaurants, maybe just toggle.
        // User asked to "add new users", let's assume standard user management.
        // We will delete the user account entirely to be safe, assuming these are dedicated admin accounts.

        $superAdmin->delete();

        return redirect()->back()->with('success', 'Super Admin deleted successfully.');
    }
}
