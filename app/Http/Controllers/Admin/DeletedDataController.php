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
        $deletedUsers = User::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->paginate(10, ['*'], 'users_page');

        $deletedRestaurants = Restaurant::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->paginate(10, ['*'], 'restaurants_page');

        return Inertia::render('Admin/DeletedData/Index', [
            'deletedUsers' => $deletedUsers,
            'deletedRestaurants' => $deletedRestaurants,
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
