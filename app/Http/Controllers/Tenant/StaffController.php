<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class StaffController extends Controller
{
    public function index(Request $request): Response
    {
        $restaurant = Restaurant::first();

        $staff = Staff::with('user')
            ->where('restaurant_id', $restaurant->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->through(function ($staff) {
                return [
                    'id' => $staff->id,
                    'name' => $staff->user->name,
                    'email' => $staff->user->email,
                    'role' => $staff->role,
                    'is_active' => $staff->is_active,
                    'joined_at' => $staff->joined_at?->format('Y-m-d'),
                ];
            });

        return Inertia::render('Staff/Manage', [
            'staff' => $staff,
            'roles' => ['owner', 'manager', 'waiter', 'chef'],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role' => ['required', 'in:owner,manager,waiter,chef'],
        ]);

        $restaurant = Restaurant::first();
        $password = Str::random(12);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);

        $staff = Staff::create([
            'user_id' => $user->id,
            'restaurant_id' => $restaurant->id,
            'role' => $validated['role'],
            'is_active' => true,
            'joined_at' => now(),
        ]);

        $user->assignRole($validated['role']);

        // TODO: Send invitation email with password

        return back()->with('success', 'Staff member added successfully. Password: ' . $password);
    }

    public function update(Request $request, Staff $staff)
    {
        $validated = $request->validate([
            'role' => ['sometimes', 'in:owner,manager,waiter,chef'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $staff->update($validated);

        if (isset($validated['role'])) {
            $staff->user->syncRoles([$validated['role']]);
        }

        return back()->with('success', 'Staff member updated successfully');
    }

    public function destroy(Staff $staff)
    {
        $staff->delete();
        return back()->with('success', 'Staff member removed successfully');
    }
}


