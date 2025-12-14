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

        // Staff Query
        $staffQuery = Staff::with('user')
            ->where('restaurant_id', $restaurant->id);

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $staffQuery->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%");
            })->orWhere('role', 'ilike', "%{$search}%");
        }

        // Sort
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        $allowedSorts = ['role', 'is_active', 'joined_at', 'created_at'];

        if (in_array($sortField, $allowedSorts)) {
            $staffQuery->orderBy($sortField, $sortDirection);
        } elseif ($sortField === 'name') {
            $staffQuery->join('users', 'staff.user_id', '=', 'users.id')
                ->orderBy('users.name', $sortDirection)
                ->select('staff.*'); // Avoid column collision
        } elseif ($sortField === 'email') {
            $staffQuery->join('users', 'staff.user_id', '=', 'users.id')
                ->orderBy('users.email', $sortDirection)
                ->select('staff.*');
        } else {
            $staffQuery->orderBy('created_at', 'desc');
        }

        $staff = $staffQuery->paginate(10)
            ->through(function ($staff) {
                return [
                    'id' => $staff->id,
                    'name' => $staff->user->name,
                    'email' => $staff->user->email,
                    'role' => $staff->role,
                    'is_active' => $staff->is_active,
                    'joined_at' => $staff->joined_at?->format('Y-m-d'),
                ];
            })
            ->withQueryString();

        return Inertia::render('Staff/Manage', [
            'staff' => $staff,
            'roles' => ['owner', 'manager', 'head_chef', 'kitchen_staff', 'waiter', 'cashier', 'delivery_driver'],
            'filters' => $request->only(['search', 'sort_field', 'sort_direction']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role' => ['required', 'in:owner,manager,head_chef,kitchen_staff,waiter,cashier,delivery_driver'],
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
            'role' => ['sometimes', 'in:owner,manager,head_chef,kitchen_staff,waiter,cashier,delivery_driver'],
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


