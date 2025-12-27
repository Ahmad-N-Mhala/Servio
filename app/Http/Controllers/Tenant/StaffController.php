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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use App\Mail\WelcomeEmail;

class StaffController extends Controller
{
    public function index(Request $request): Response
    {
        $restaurant = Restaurant::find(session('active_restaurant_id')) ?? Restaurant::first();

        // Ensure Owner is in Staff list
        $ownerEmails = [];

        // 1. Check Owner Email from restaurant record
        if ($restaurant->email && is_string($restaurant->email)) {
            $ownerEmails[] = $restaurant->email;
        }

        // 2. Check Owner from pivot table
        $pivotOwners = \Illuminate\Support\Facades\DB::table('restaurant_user')
            ->where('restaurant_id', (string) $restaurant->id)
            ->where('role', 'owner')
            ->pluck('email')
            ->toArray();

        foreach ($pivotOwners as $email) {
            if (is_string($email)) {
                $ownerEmails[] = $email;
            }
        }

        $ownerEmails = array_unique(array_filter($ownerEmails));

        if (!empty($ownerEmails)) {
            $ownerUsers = User::whereIn('email', $ownerEmails)->get();

            foreach ($ownerUsers as $ownerUser) {
                // Check if staff record exists
                $exists = Staff::where('restaurant_id', $restaurant->id)
                    ->where('user_id', $ownerUser->id)
                    ->exists();

                if (!$exists) {
                    Staff::create([
                        'user_id' => $ownerUser->id,
                        'restaurant_id' => $restaurant->id,
                        'role' => 'owner',
                        'is_active' => true,
                        'joined_at' => now(),
                    ]);

                    // Also ensure they have the role
                    if (!$ownerUser->hasRole('owner')) {
                        $ownerUser->assignRole('owner');
                    }
                }
            }
        }

        // Staff Query
        $staffQuery = Staff::with('user')
            ->where('restaurant_id', $restaurant->id)
            ->whereHas('user');

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $staffQuery->where(function ($query) use ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    // MongoDB uses regex for like search usually; standard 'like' works in Laravel-Mongo wrapper
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('role', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        $allowedSorts = ['role', 'is_active', 'joined_at', 'created_at'];

        if (in_array($sortField, $allowedSorts)) {
            $staffQuery->orderBy($sortField, $sortDirection);
        } else {
            // For name/email, standard MongoDB cannot sort by lookup aggregation easily in Eloquent
            // We fallback to created_at for now to avoid crash
            $staffQuery->orderBy('created_at', 'desc');
        }

        $staff = $staffQuery->paginate(10)
            ->through(function ($staff) {
                return [
                    'id' => $staff->id,
                    'name' => $staff->user->name ?? 'Unknown',
                    'email' => $staff->user->email ?? 'Unknown',
                    'role' => $staff->role,
                    'is_active' => $staff->is_active,
                    'joined_at' => $staff->joined_at?->format('Y-m-d'),
                ];
            })
            ->withQueryString();

        return Inertia::render('Staff/Manage', [
            'staff' => $staff,
            'roles' => array_keys(config('roles.display_names')),
            'filters' => $request->only(['search', 'sort_field', 'sort_direction']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role' => ['required', config('roles.validation_rule')],
        ]);

        $restaurant = Restaurant::find(session('active_restaurant_id')) ?? Restaurant::first();
        $password = Str::random(12);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);

        // Force add restaurant_id to user as requested
        $user->forceFill(['restaurant_id' => $restaurant->id])->save();

        $staff = Staff::create([
            'user_id' => $user->id,
            'restaurant_id' => $restaurant->id,
            'role' => $validated['role'],
            'is_active' => true,
            'joined_at' => now(),
        ]);

        $user->assignRole($validated['role']);

        // Add to restaurant_user pivot table
        \Illuminate\Support\Facades\DB::table('restaurant_user')->insert([
            'email' => $validated['email'],
            'restaurant_id' => (string) $restaurant->id,
            'role' => $validated['role'],
            // MongoDB supports timestamps; useful to have
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Send invitation email with password reset link
        $token = Password::createToken($user);
        $resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);

        try {
            Mail::to($user->email)->send(new WelcomeEmail($user, $restaurant->name, $resetUrl));
        } catch (\Exception $e) {
            // Log error but don't fail the request completely
            \Log::error('Failed to send welcome email: ' . $e->getMessage());
        }

        return back()->with('success', 'Staff member added successfully. An invitation email has been sent.');
    }

    public function update(Request $request, Staff $staff)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:users,email,' . $staff->user_id],
            'role' => ['sometimes', config('roles.validation_rule')],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $user = $staff->user;
        $oldEmail = $user->email;

        // Update User details
        $userUpdateData = [];
        if (isset($validated['name']))
            $userUpdateData['name'] = $validated['name'];
        if (isset($validated['email']))
            $userUpdateData['email'] = $validated['email'];

        if (!empty($userUpdateData)) {
            $user->update($userUpdateData);
        }

        // Update Staff details
        if (isset($validated['is_active'])) {
            $staff->update(['is_active' => $validated['is_active']]);
        }

        // Update Pivot Table (Role and Email)
        if (isset($validated['role']) || isset($validated['email'])) {
            $query = \Illuminate\Support\Facades\DB::table('restaurant_user')
                ->where('email', $oldEmail) // Use old email to find the record
                ->where('restaurant_id', (string) $staff->restaurant_id);

            $pivotUpdates = ['updated_at' => now()];

            if (isset($validated['role'])) {
                $pivotUpdates['role'] = $validated['role'];
                $user->syncRoles([$validated['role']]);
            }

            if (isset($validated['email'])) {
                $pivotUpdates['email'] = $validated['email'];
            }

            $query->update($pivotUpdates);
        }

        return back()->with('success', 'Staff member updated successfully');
    }

    public function destroy(Staff $staff)
    {
        // 1. Check for Last Owner
        if ($staff->role === 'owner') {
            // Count owners for this restaurant
            $ownerCount = Staff::where('restaurant_id', $staff->restaurant_id)
                ->where('role', 'owner')
                ->count();

            if ($ownerCount <= 1) {
                return back()->with('error', 'Cannot delete the only owner of the restaurant.');
            }
        }

        // 2. Remove from restaurant_user pivot
        \Illuminate\Support\Facades\DB::table('restaurant_user')
            ->where('email', $staff->user->email)
            ->where('restaurant_id', (string) $staff->restaurant_id)
            ->delete();

        // 3. Delete the User record
        // Note: This permanently deletes the user account. 
        // If the user belongs to OTHER restaurants, this logic shouldn't be used, but per request "remove from DB":
        if ($staff->user) {
            $staff->user->delete();
        }

        // 4. Delete Staff record
        $staff->delete();

        return back()->with('success', 'Staff member and user account removed successfully');
    }
}
