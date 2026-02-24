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

use App\Models\StaffLog;

class StaffController extends Controller
{
    public function logs(Request $request, Staff $staff)
    {
        $logs = StaffLog::where('user_id', $staff->user_id)
            ->latest()
            ->paginate(50)
            ->through(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'changes' => $log->changes,
                    'causer_name' => $log->causer_name ?? 'System',
                    'date' => $log->created_at->format('Y-m-d H:i:s'),
                    'ip' => $log->ip_address
                ];
            });

        return response()->json($logs);
    }

    public function index(Request $request): Response
    {
        $restaurant = auth()->user()->currentRestaurant();

        if (!$restaurant) {
            abort(404, 'Restaurant context not found');
        }

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
                    'phone' => $staff->user->phone ?? 'N/A', // Added phone
                    'role' => $staff->role,
                    'is_active' => $staff->is_active,
                    'joined_at' => $staff->joined_at?->format('Y-m-d'),
                ];
            })
            ->withQueryString();

        // Calculate Stats
        $restaurantId = (string) $restaurant->id;
        $allStaff = Staff::withoutGlobalScopes()
            ->where('restaurant_id', $restaurantId)
            ->whereHas('user')
            ->get();

        $stats = [
            'total' => $allStaff->count(),
            'active' => $allStaff->where('is_active', true)->count(),
            'by_role' => $allStaff->groupBy('role')->map->count(),
        ];


        // Fetch Roles dynamically
        $dbRoles = \App\Models\Role::all();
        $configNames = config('roles.display_names', []);
        $locale = app()->getLocale();

        $rolesList = [];
        foreach ($dbRoles as $role) {
            $label = null;
            $display = (!empty($role->display_name) && (is_array($role->display_name) || is_object($role->display_name)))
                ? (array) $role->display_name
                : [];

            // 1. DB Display Name (Current Locale)
            if (!empty($display[$locale])) {
                $label = $display[$locale];
            }

            // 2. Translation File
            if (!$label && \Illuminate\Support\Facades\Lang::has('roles.' . $role->name)) {
                $label = __('roles.' . $role->name);
            }

            // 3. DB Display Name (English Fallback)
            if (!$label && !empty($display['en'])) {
                $label = $display['en'];
            }

            // 4. Config
            if (!$label) {
                $label = $configNames[$role->name] ?? null;
            }

            // 5. Fallback
            if (!$label) {
                $label = ucwords(str_replace('_', ' ', $role->name));
            }

            $rolesList[] = [
                'value' => $role->name,
                'label' => $label
            ];
        }

        return Inertia::render('Staff/Manage', [
            'staff' => $staff,
            'stats' => $stats,
            'roles' => $rolesList,
            'filters' => $request->only(['search', 'sort_field', 'sort_direction']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'], // Ask for phone
            'role' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!\App\Models\Role::where('name', $value)->exists()) {
                        $fail('The selected role is invalid.');
                    }
                }
            ],
        ]);

        $restaurant = auth()->user()->currentRestaurant();
        if (!$restaurant) {
            abort(404, 'Restaurant context not found');
        }

        $password = Str::random(12);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
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

        $commService = app(\App\Services\CommunicationService::class);
        $commService->sendNotification('user_registered', $user, [
            'link' => $resetUrl
        ]);

        // Log Staff Creation
        StaffLog::create([
            'staff_id' => $staff->id,
            'user_id' => $user->id,
            'action' => 'User Created',
            'changes' => [
                'name' => ['new' => $validated['name']],
                'email' => ['new' => $validated['email']],
                'phone' => ['new' => $validated['phone']],
                'role' => ['new' => $validated['role']],
                'is_active' => ['new' => true]
            ],
            'causer_id' => auth()->id(),
            'causer_name' => auth()->user()->name,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return back()->with('success', 'Staff member added successfully. An invitation email has been sent.');
    }

    public function update(Request $request, Staff $staff)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:users,email,' . $staff->user_id],
            'phone' => ['sometimes', 'string', 'max:20'],
            'role' => [
                'sometimes',
                'string',
                function ($attribute, $value, $fail) {
                    if (!\App\Models\Role::where('name', $value)->exists()) {
                        $fail('The selected role is invalid.');
                    }
                }
            ],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $user = $staff->user;
        $oldEmail = $user->email;


        // Capture Old Data for Logging
        $oldUserData = [
            'name' => $user->name,
            'phone' => $user->phone,
        ];
        $oldStaffData = [
            'is_active' => $staff->is_active,
            'role' => $staff->role,
        ];

        // Update User details
        $userUpdateData = [];
        if (isset($validated['name']) && $validated['name'] !== $oldUserData['name'])
            $userUpdateData['name'] = $validated['name'];
        // Email is tricky because we use oldEmail for pivot lookup, but let's check change
        if (isset($validated['email']) && $validated['email'] !== $oldEmail)
            $userUpdateData['email'] = $validated['email'];
        if (isset($validated['phone']) && $validated['phone'] !== $oldUserData['phone'])
            $userUpdateData['phone'] = $validated['phone'];

        if (!empty($userUpdateData)) {
            $user->update($userUpdateData);
        }

        // Update Staff details
        $staffUpdates = [];
        if (isset($validated['is_active']) && $validated['is_active'] !== $oldStaffData['is_active']) {
            $staffUpdates['is_active'] = $validated['is_active'];
        }
        if (isset($validated['role']) && $validated['role'] !== $oldStaffData['role']) {
            $staffUpdates['role'] = $validated['role'];
        }

        if (!empty($staffUpdates)) {
            $staff->update($staffUpdates);
        }

        // Log the changes
        $changes = [];

        // Track User Changes
        foreach ($userUpdateData as $key => $newValue) {
            $changes[$key] = [
                'old' => $key === 'email' ? $oldEmail : ($oldUserData[$key] ?? null),
                'new' => $newValue
            ];
        }

        // Track Staff Changes
        foreach ($staffUpdates as $key => $newValue) {
            $changes[$key] = [
                'old' => $oldStaffData[$key] ?? null,
                'new' => $newValue
            ];
        }

        // Log to StaffLog if there are any changes
        if (!empty($changes)) {
            StaffLog::create([
                'staff_id' => $staff->id,
                'user_id' => $staff->user_id,
                'action' => 'User Updated',
                'changes' => $changes,
                'causer_id' => auth()->id(),
                'causer_name' => auth()->user()->name,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
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
            // Count owners for this restaurant from Staff table
            $ownerCount = Staff::where('restaurant_id', $staff->restaurant_id)
                ->where('role', 'owner')
                ->count();

            // Also check the pivot table for accuracy
            $pivotOwnerCount = \Illuminate\Support\Facades\DB::table('restaurant_user')
                ->where('restaurant_id', (string) $staff->restaurant_id)
                ->where('role', 'owner')
                ->count();

            // Use the maximum finding to be safe
            if ($ownerCount <= 1 || $pivotOwnerCount <= 1) {
                return back()->with('error', 'Cannot delete the only owner of the restaurant. There must be at least one owner per restaurant.');
            }
        }

        // 2. Remove from restaurant_user pivot
        \Illuminate\Support\Facades\DB::table('restaurant_user')
            ->where('email', $staff->user->email)
            ->where('restaurant_id', (string) $staff->restaurant_id)
            ->delete();

        // 3. Check if user belongs to other restaurants before deleting the User account
        $otherAssociations = \Illuminate\Support\Facades\DB::table('restaurant_user')
            ->where('email', $staff->user->email)
            ->count();

        if ($otherAssociations === 0) {
            // User has no other restaurants, safe to delete user account
            if ($staff->user) {
                $staff->user->delete();
            }
        }

        // 4. Delete Staff record
        $staff->delete();

        return back()->with('success', 'Staff member removed successfully');
    }
}
