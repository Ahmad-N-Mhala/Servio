<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::query()->where('is_super_admin', '!=', true);

        if ($request->input('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('search') . '%')
                    ->orWhere('email', 'like', '%' . $request->input('search') . '%')
                    ->orWhere('phone', 'like', '%' . $request->input('search') . '%');
            });
        }

        $users = $query->latest()->paginate(10)->appends([
            'search' => $request->input('search'),
        ]);

        // Manually attach restaurant names because MongoDB relationship with pivot can be tricky
        // and we want efficient fetching.

        $users->getCollection()->transform(function ($user) {
            // Fetch restaurant IDs from pivot manually if relation is flaky, or try relation first.
            // Given previous issues with 'owner' relation, manual fetch via pivot table is safest.
            $restaurantIds = \Illuminate\Support\Facades\DB::connection('mongodb')
                ->table('restaurant_user')
                ->where('email', $user->email)
                ->pluck('restaurant_id')
                ->toArray();

            $restaurantNames = [];
            if (!empty($restaurantIds)) {
                $restaurantNames = \App\Models\Restaurant::whereIn('id', $restaurantIds)
                    ->pluck('name')
                    ->toArray();
            }

            $user->restaurant_names = implode(', ', $restaurantNames);
            return $user;
        });

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
