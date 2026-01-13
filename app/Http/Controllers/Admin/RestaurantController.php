<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    public function index(Request $request)
    {
        // Include soft-deleted restaurants
        $query = \App\Models\Restaurant::withTrashed()->with(['subscription.plan']);

        if ($request->input('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        if ($request->input('restaurant_id')) {
            $query->where('id', $request->input('restaurant_id'));
        }

        // Filter by status (active, deleted, all)
        if ($request->input('status') === 'deleted') {
            $query->onlyTrashed();
        } elseif ($request->input('status') === 'active') {
            $query->whereNull('deleted_at');
        }
        // Default: show all (withTrashed already applied)

        $restaurantOptions = \App\Models\Restaurant::select('id', 'name')
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        $restaurants = $query->latest()->paginate(10)->appends([
            'search' => $request->input('search'),
            'restaurant_id' => $request->input('restaurant_id'),
            'status' => $request->input('status')
        ]);

        // Manually attach owner (logic remains same)
        $restaurants->getCollection()->transform(function ($restaurant) {
            $ownerPivot = \Illuminate\Support\Facades\DB::table('restaurant_user')
                ->where('restaurant_id', (string) $restaurant->id)
                ->where('role', 'owner')
                ->first();

            $restaurant->owner = $ownerPivot
                ? \App\Models\User::where('email', $ownerPivot->email)->first()
                : null;

            return $restaurant;
        });

        // Fetch plans for subscription management
        $plans = \App\Models\Plan::where('is_active', true)->get();

        return inertia('Admin/Restaurants/Index', [
            'restaurants' => $restaurants,
            'filters' => $request->only(['search', 'restaurant_id', 'status']),
            'restaurantOptions' => $restaurantOptions,
            'plans' => $plans,
        ]);
    }

    public function create()
    {
        $plans = \App\Models\Plan::where('is_active', true)->get();
        return inertia('Admin/Restaurants/Create', [
            'plans' => $plans
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:restaurants',
            // Owner Details (required for creation)
            'owner_name' => 'required|string|max:255',
            'owner_email' => 'required|email|unique:users,email',
            'owner_phone' => 'required|string',
            'owner_password' => 'required|string|min:8',

            // Location & Details
            'phone' => 'nullable|string', // Restaurant Phone
            'email' => 'required|email', // Restaurant Email
            'currency' => 'required|string|size:3',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'zip_code' => 'nullable|string',
            'country' => 'nullable|string',
            'google_map_location' => 'nullable|url',

            // Loyalty
            'earning_method_type' => 'nullable|string|in:order_total,visit',
            'earning_points' => 'nullable|numeric|min:1',
            'earning_min_spent' => 'nullable|numeric|min:0',
            'earning_max_points' => 'nullable|numeric|min:1',
            'earning_currency_amount' => 'nullable|numeric|min:0.01',

            // Subscription
            'plan_id' => 'required|exists:plans,id',
            'billing_cycle' => 'required|in:monthly,yearly',

            // Service Type
            'service_type' => 'required|string|in:self_service,table_service,both',

            // Logo
            'logo' => 'nullable|image|max:2048',
        ]);

        try {
            // \DB::beginTransaction();

            // Handle Logo
            $logoPath = null;
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('restaurants/logos', 'public');
            }

            // 1. Create Restaurant
            $restaurantData = $request->only([
                'name',
                'slug',
                'email',
                'phone',
                'currency',
                'address',
                'city',
                'state',
                'zip_code',
                'country',
                'google_map_location',
                'service_type'
            ]);

            if ($logoPath) {
                $restaurantData['logo'] = $logoPath;
            }

            $restaurant = \App\Models\Restaurant::create($restaurantData);

            // 2. Create Owner User
            $user = \App\Models\User::create([
                'name' => $request->owner_name,
                'email' => $request->owner_email,
                'phone' => $request->owner_phone,
                'password' => \Illuminate\Support\Facades\Hash::make($request->owner_password),
                'is_super_admin' => false,
            ]);

            // 3. Attach User to Restaurant (Owner Role)
            \Illuminate\Support\Facades\DB::table('restaurant_user')->insert([
                'restaurant_id' => (string) $restaurant->id,
                'email' => $user->email,
                'role' => 'owner'
            ]);

            // Check if plan has loyalty feature
            $plan = \App\Models\Plan::find($request->plan_id);
            $planFeatures = $plan ? ($plan->enabled_features ?? []) : [];
            $hasLoyalty = in_array('loyalty', $planFeatures);

            // 4. Create Default Loyalty Setting (Conditional)
            if ($hasLoyalty && ($request->earning_method_type || $request->earning_points)) {
                \App\Models\EarningMethod::create([
                    'restaurant_id' => $restaurant->id,
                    'name' => $request->earning_method_type === 'order_total' ? 'Points per Spend' : 'Points per Visit',
                    'type' => $request->earning_method_type ?? 'order_total',
                    'points' => $request->earning_points ?? 1,
                    'is_active' => true,
                    'currency_amount' => ($request->earning_method_type ?? 'order_total') === 'order_total' ? ($request->earning_currency_amount ?? 1) : null,
                    'min_spent' => $request->earning_min_spent ?? 0,
                    'max_points' => $request->earning_max_points,
                ]);
            }

            // 5. Create Subscription
            if ($request->has('plan_id')) {
                $plan = \App\Models\Plan::find($request->plan_id);
                if ($plan) {
                    \App\Models\RestaurantSubscription::create([
                        'restaurant_id' => $restaurant->id,
                        'plan_id' => $plan->id,
                        'status' => 'active', // Admin created, so assume active/paid/free bypass
                        'billing_cycle' => $request->billing_cycle ?? 'monthly',
                        'starts_at' => now(),
                        'ends_at' => ($request->billing_cycle ?? 'monthly') === 'yearly' ? now()->addYear() : now()->addMonth(),
                    ]);
                }
            }

            // Send Welcome/Creation Notification
            // Try System Template First
            $commService = app(\App\Services\CommunicationService::class);
            $commService->sendNotification('restaurant_created', $user, [
                'restaurant_name' => $restaurant->name,
                'link' => route('login'), // Or setup link
                'owner_email' => $user->email,
                'owner_password' => $request->owner_password,
            ]);

            // \DB::commit();

            return redirect()->route('admin.restaurants.index')
                ->with('success', 'Restaurant and Owner account created successfully.');

        } catch (\Exception $e) {
            // \DB::rollBack();
            return back()->with('error', 'Creation failed: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(\App\Models\Restaurant $restaurant)
    {
        // Load earning method from the EarningMethod model
        $earningMethod = \App\Models\EarningMethod::where('restaurant_id', $restaurant->id)->where('is_active', true)->first();

        $restaurantData = $restaurant->toArray();
        if ($earningMethod) {
            $restaurantData['earning_method_type'] = $earningMethod->type;
            $restaurantData['earning_points'] = $earningMethod->points;
            $restaurantData['earning_method_name_en'] = is_array($earningMethod->name) ? ($earningMethod->name['en'] ?? '') : $earningMethod->name;
            $restaurantData['earning_method_name_ar'] = is_array($earningMethod->name) ? ($earningMethod->name['ar'] ?? '') : '';
            $restaurantData['earning_method_description'] = $earningMethod->description;
            $restaurantData['earning_currency_amount'] = $earningMethod->currency_amount;
            $restaurantData['earning_min_spent'] = $earningMethod->min_spent;
            $restaurantData['earning_max_points'] = $earningMethod->max_points;
            $restaurantData['earning_is_active'] = $earningMethod->is_active;
        }

        // Load Owner Email specifically
        $ownerRef = \DB::table('restaurant_user')
            ->where('restaurant_id', $restaurant->id)
            ->where('role', 'owner')
            ->first();

        $restaurantData['owner_email'] = $ownerRef ? $ownerRef->email : $restaurant->email;

        return inertia('Admin/Restaurants/Edit', [
            'restaurant' => $restaurantData
        ]);
    }

    public function update(Request $request, \App\Models\Restaurant $restaurant)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'slug' => 'required|string|max:255|unique:restaurants,slug,' . $restaurant->id,
                'email' => 'required|email',
                'phone' => 'nullable|string',
                'currency' => 'required|string|size:3',
                'status' => 'required|string|in:active,suspended',
                'address' => 'nullable|string',
                'city' => 'nullable|string',
                'state' => 'nullable|string',
                'zip_code' => 'nullable|string',
                'country' => 'nullable|string',
                'google_map_location' => 'nullable|url',
                'service_type' => 'required|string|in:self_service,table_service,both',

                // Loyalty
                'earning_method_type' => 'nullable|string|in:order_total,visit',
                'earning_points' => 'nullable|numeric|min:1',
                'earning_method_name_en' => 'nullable|string|max:255',
                'earning_method_name_ar' => 'nullable|string|max:255',
                'earning_method_description' => 'nullable|string',
                'earning_currency_amount' => 'nullable|numeric|min:0.01',
                'earning_min_spent' => 'nullable|numeric|min:0',
                'earning_max_points' => 'nullable|numeric|min:1',
                'earning_is_active' => 'nullable|boolean',

                // Owner Updates
                'new_owner_name' => 'nullable|string|max:255',
                'new_owner_email' => 'nullable|email|unique:users,email',
                'new_owner_phone' => 'nullable|string',
                'new_owner_password' => 'nullable|string|min:8',
            ]);

            // \DB::beginTransaction();

            $restaurant->update($validated);

            // Update Earning Method Settings (comprehensive)
            if ($request->has('earning_method_type') && $request->has('earning_points')) {
                \App\Models\EarningMethod::updateOrCreate(
                    ['restaurant_id' => $restaurant->id],
                    [
                        'name' => [
                            'en' => $request->earning_method_name_en ?? 'Loyalty Points',
                            'ar' => $request->earning_method_name_ar ?? 'نقاط الولاء',
                        ],
                        'description' => $request->earning_method_description,
                        'type' => $request->earning_method_type,
                        'points' => $request->earning_points,
                        'currency_amount' => $request->earning_method_type === 'order_total' ? ($request->earning_currency_amount ?? 1) : null,
                        'min_spent' => $request->earning_min_spent,
                        'max_points' => $request->earning_max_points,
                        'is_active' => $request->earning_is_active ?? true,
                    ]
                );
            }

            // Handle Owner Info Changes (Name, Email, Phone, Password)
            if ($request->filled('new_owner_email') || $request->filled('new_owner_password') || $request->filled('new_owner_name') || $request->filled('new_owner_phone')) {

                // Get the current owner from pivot
                $currentOwnerPivot = \Illuminate\Support\Facades\DB::table('restaurant_user')
                    ->where('restaurant_id', (string) $restaurant->id)
                    ->where('role', 'owner')
                    ->first();

                if ($currentOwnerPivot) {
                    $user = \App\Models\User::where('email', $currentOwnerPivot->email)->first();

                    if ($user) {
                        // Update User Fields
                        if ($request->filled('new_owner_name'))
                            $user->name = $request->new_owner_name;
                        if ($request->filled('new_owner_phone'))
                            $user->phone = $request->new_owner_phone;

                        // Handle Email Change
                        if ($request->filled('new_owner_email') && $request->new_owner_email !== $user->email) {
                            $oldEmail = $user->email;
                            $user->email = $request->new_owner_email;

                            // Update Pivot
                            \Illuminate\Support\Facades\DB::table('restaurant_user')
                                ->where('restaurant_id', (string) $restaurant->id)
                                ->where('email', $oldEmail)
                                ->update(['email' => $request->new_owner_email]);

                            // Update Restaurant Email if matched? (Usually separate, but kept separate in logic above)
                        }

                        // Handle Password
                        if ($request->filled('new_owner_password')) {
                            $user->password = \Illuminate\Support\Facades\Hash::make($request->new_owner_password);
                        }

                        $user->save();
                    }
                }
            }

            // \DB::commit();

            return redirect()->route('admin.restaurants.index')
                ->with('success', 'Restaurant and Owner details updated successfully.');

        } catch (\Exception $e) {
            // \DB::rollBack();
            return back()->with('error', 'Update failed: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(\App\Models\Restaurant $restaurant)
    {
        try {
            // Soft delete the restaurant (sets deleted_at timestamp)
            $restaurant->delete();

            // Deactivate all users associated with this restaurant in the pivot table
            \Illuminate\Support\Facades\DB::table('restaurant_user')
                ->where('restaurant_id', (string) $restaurant->id)
                ->update([
                    'is_active' => false,
                    'updated_at' => now()
                ]);

            // Update restaurant status to inactive
            $restaurant->update(['status' => 'inactive']);

            return redirect()->route('admin.restaurants.index')
                ->with('success', 'Restaurant has been deleted and all associated users have been deactivated.');
        } catch (\Exception $e) {
            return redirect()->route('admin.restaurants.index')
                ->with('error', 'Failed to delete restaurant: ' . $e->getMessage());
        }
    }

    /**
     * Restore a soft-deleted restaurant and reactivate its users
     */
    /**
     * Restore a soft-deleted restaurant and reactivate its users
     */
    public function restore($id)
    {
        try {
            // Find the soft-deleted restaurant
            $restaurant = \App\Models\Restaurant::withTrashed()->findOrFail($id);

            // Restore the restaurant (removes deleted_at timestamp)
            $restaurant->restore();

            // Reactivate all users associated with this restaurant in the pivot table
            \Illuminate\Support\Facades\DB::table('restaurant_user')
                ->where('restaurant_id', (string) $restaurant->id)
                ->update([
                    'is_active' => true,
                    'updated_at' => now()
                ]);

            // Update restaurant status to active
            $restaurant->update(['status' => 'active']);

            return redirect()->route('admin.restaurants.index')
                ->with('success', 'Restaurant has been restored and all associated users have been reactivated.');
        } catch (\Exception $e) {
            return redirect()->route('admin.restaurants.index')
                ->with('error', 'Failed to restore restaurant: ' . $e->getMessage());
        }
    }

    /**
     * Permanently delete a restaurant and all related data
     */
    public function forceDestroy($id)
    {
        try {
            $restaurant = \App\Models\Restaurant::withTrashed()->findOrFail($id);

            // \DB::beginTransaction();

            // 1. Detach/Delete Users
            // We only delete the association in the pivot table, not the actual User account 
            // unless they are exclusively tied to this restaurant? 
            // For safety in this multi-tenant setup, we'll just remove the access.
            \Illuminate\Support\Facades\DB::table('restaurant_user')
                ->where('restaurant_id', (string) $restaurant->id)
                ->delete();

            // 2. Delete Subscriptions
            \App\Models\RestaurantSubscription::where('restaurant_id', $restaurant->id)->delete();

            // 3. Delete Loyalty/Earning Methods
            \App\Models\EarningMethod::where('restaurant_id', $restaurant->id)->delete();

            // 4. Delete Staff
            \App\Models\Staff::where('restaurant_id', $restaurant->id)->delete();

            // 5. Delete Orders (and related items)
            \App\Models\Order::where('restaurant_id', $restaurant->id)->delete();

            // 6. Delete Menu Items/Categories
            \App\Models\MenuCategory::where('restaurant_id', $restaurant->id)->delete();
            \App\Models\MenuItem::where('restaurant_id', $restaurant->id)->delete();

            // 7. Finally, Force Delete the Restaurant
            $restaurant->forceDelete();

            // \DB::commit();

            return redirect()->route('admin.restaurants.index')
                ->with('success', 'Restaurant and all related data have been permanently deleted.');
        } catch (\Exception $e) {
            // \DB::rollBack();
            return redirect()->route('admin.restaurants.index')
                ->with('error', 'Failed to permanently delete restaurant: ' . $e->getMessage());
        }
    }
}
