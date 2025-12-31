<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Restaurant;
use Inertia\Inertia;

class DeletedDataController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $usersQuery = User::onlyTrashed()->with('restaurants');

        if ($search) {
            $usersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $deletedUsers = $usersQuery->orderBy('deleted_at', 'desc')
            ->paginate(10, ['*'], 'users_page')
            ->withQueryString();

        $restaurantsQuery = Restaurant::onlyTrashed();

        if ($search) {
            $restaurantsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $deletedRestaurants = $restaurantsQuery->orderBy('deleted_at', 'desc')
            ->paginate(10, ['*'], 'restaurants_page')
            ->withQueryString();

        return Inertia::render('Admin/DeletedData/Index', [
            'deletedUsers' => $deletedUsers,
            'deletedRestaurants' => $deletedRestaurants,
            'filters' => $request->only('search'),
        ]);
    }

    public function restoreUser($id)
    {
        try {
            $user = User::withTrashed()->findOrFail($id);
            $user->restore();

            return redirect()->back()->with('success', 'User restored successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to restore user: ' . $e->getMessage());
        }
    }
}
