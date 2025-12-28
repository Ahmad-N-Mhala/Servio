<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\Restaurant::with(['subscription.plan'])->where('status', '!=', 'deleted');

        if ($request->input('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        if ($request->input('restaurant_id')) {
            $query->where('id', $request->input('restaurant_id'));
        }

        $restaurantOptions = \App\Models\Restaurant::select('id', 'name')
            ->where('status', '!=', 'deleted')
            ->orderBy('name')
            ->get();

        $restaurants = $query->latest()->paginate(10)->appends([
            'search' => $request->input('search'),
            'restaurant_id' => $request->input('restaurant_id')
        ]);

        // Manually attach owner to avoid MongoDB Relation issues with Pivot filtering
        $restaurants->getCollection()->transform(function ($restaurant) {
            $ownerPivot = \Illuminate\Support\Facades\DB::table('restaurant_user')
                ->where('restaurant_id', $restaurant->id)
                ->where('role', 'owner')
                ->first();

            $restaurant->owner = $ownerPivot
                ? \App\Models\User::where('email', $ownerPivot->email)->first()
                : null;

            return $restaurant;
        });

        return inertia('Admin/Restaurants/Index', [
            'restaurants' => $restaurants,
            'filters' => $request->only(['search', 'restaurant_id']),
            'restaurantOptions' => $restaurantOptions,
        ]);
    }

    public function create()
    {
        return inertia('Admin/Restaurants/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:restaurants',
            'email' => 'required|email',
            'phone' => 'nullable|string',
            'currency' => 'required|string|size:3',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'zip_code' => 'nullable|string',
            'country' => 'nullable|string',
            'earning_method_type' => 'nullable|string|in:order_total,visit',
            'earning_points' => 'nullable|numeric|min:1',
        ]);

        $restaurant = \App\Models\Restaurant::create($validated);

        // Create Default Loyalty Setting
        \App\Models\EarningMethod::create([
            'restaurant_id' => $restaurant->id,
            'name' => $request->earning_method_type === 'order_total' ? 'Points per Spend' : 'Points per Visit',
            'type' => $request->earning_method_type ?? 'order_total',
            'points' => $request->earning_points ?? 1,
            'is_active' => true,
            'currency_amount' => ($request->earning_method_type ?? 'order_total') === 'order_total' ? 1 : null,
        ]);

        return redirect()->route('admin.restaurants.index')
            ->with('success', 'Restaurant created successfully.');
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
                'earning_method_type' => 'nullable|string|in:order_total,visit',
                'earning_points' => 'nullable|numeric|min:1',
                'earning_method_name_en' => 'nullable|string|max:255',
                'earning_method_name_ar' => 'nullable|string|max:255',
                'earning_method_description' => 'nullable|string',
                'earning_currency_amount' => 'nullable|numeric|min:0.01',
                'earning_min_spent' => 'nullable|numeric|min:0',
                'earning_max_points' => 'nullable|numeric|min:1',
                'earning_is_active' => 'nullable|boolean',
                'new_owner_email' => 'nullable|email|unique:users,email',
                'new_owner_password' => 'nullable|string|min:8',
            ]);

            \DB::beginTransaction();

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

            // Handle Owner Email Change
            if ($request->filled('new_owner_email')) {
                // Get the current owner email from restaurant_user pivot table
                $currentOwner = \DB::table('restaurant_user')
                    ->where('restaurant_id', $restaurant->id)
                    ->where('role', 'owner')
                    ->first();

                if ($currentOwner) {
                    // Find the user by current email
                    $user = \App\Models\User::where('email', $currentOwner->email)->first();

                    if ($user) {
                        // Update user email
                        $user->email = $request->new_owner_email;
                        $user->save();

                        // Update pivot table
                        \DB::table('restaurant_user')
                            ->where('restaurant_id', $restaurant->id)
                            ->where('email', $currentOwner->email)
                            ->update(['email' => $request->new_owner_email]);

                        // Update restaurant email
                        $restaurant->update(['email' => $request->new_owner_email]);
                    }
                }
            }

            // Handle Owner Password Reset
            if ($request->filled('new_owner_password')) {
                // Get the owner from restaurant_user pivot table
                $ownerPivot = \DB::table('restaurant_user')
                    ->where('restaurant_id', $restaurant->id)
                    ->where('role', 'owner')
                    ->first();

                if ($ownerPivot) {
                    // Find and update the user's password
                    $user = \App\Models\User::where('email', $ownerPivot->email)->first();

                    if ($user) {
                        $user->password = \Hash::make($request->new_owner_password);
                        $user->save();
                    }
                }
            }

            \DB::commit();

            return redirect()->route('admin.restaurants.index')
                ->with('success', 'Restaurant updated successfully.' .
                    ($request->filled('new_owner_email') ? ' Owner email changed.' : '') .
                    ($request->filled('new_owner_password') ? ' Owner password reset.' : ''));

        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->with('error', 'Update failed: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(\App\Models\Restaurant $restaurant)
    {
        try {
            // 1. Get emails of all users associated with this restaurant
            $userEmails = \Illuminate\Support\Facades\DB::table('restaurant_user')
                ->where('restaurant_id', $restaurant->id)
                ->pluck('email');

            // 2. Delete the users from the central 'users' table
            \App\Models\User::whereIn('email', $userEmails)->delete();

            // 3. Delete from the pivot table 'restaurant_user'
            \Illuminate\Support\Facades\DB::table('restaurant_user')
                ->where('restaurant_id', $restaurant->id)
                ->delete();

            // 4. Delete related staff records
            \App\Models\Staff::where('restaurant_id', $restaurant->id)->delete();

            // 5. Delete the restaurant record itself
            $restaurant->delete();

            return redirect()->route('admin.restaurants.index')
                ->with('success', 'Restaurant and all associated users have been permanently deleted.');
        } catch (\Exception $e) {
            return redirect()->route('admin.restaurants.index')
                ->with('error', 'Failed to delete restaurant: ' . $e->getMessage());
        }
    }
}
