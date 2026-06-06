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
                $q->where('name', 'like', '%'.$request->input('search').'%')
                    ->orWhere('email', 'like', '%'.$request->input('search').'%')
                    ->orWhere('phone', 'like', '%'.$request->input('search').'%');
            });
        }

        // Export Logic
        if ($request->boolean('export')) {
            return $this->exportUsers($query);
        }

        $users = $query->latest()->paginate(10)->appends([
            'search' => $request->input('search'),
        ]);

        // Manually attach restaurant names AND roles
        $users->getCollection()->transform(function ($user) {
            $pivotRows = \Illuminate\Support\Facades\DB::connection('mongodb')
                ->table('restaurant_user')
                ->where('email', $user->email)
                ->get();

            $restaurantIds = collect($pivotRows)->pluck('restaurant_id')->toArray();

            $restaurantNames = [];
            if (! empty($restaurantIds)) {
                $restaurantNames = \App\Models\Restaurant::whereIn('id', $restaurantIds)
                    ->pluck('name')
                    ->toArray();
            }

            // Extract Roles
            $roles = collect($pivotRows)->pluck('role')->filter()->unique()->toArray();

            $user->restaurant_names = implode(', ', $restaurantNames);
            $user->roles_list = implode(', ', $roles);

            return $user;
        });

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search']),
        ]);
    }

    protected function exportUsers($query)
    {
        $users = $query->latest()->get();
        // Generate CSV stream
        $callback = function () use ($users) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Name', 'Email', 'Phone', 'Joined', 'Last Login', 'Restaurants', 'Roles']);

            foreach ($users as $user) {
                // Fetch Restaurants & Roles (Replicate Logic)
                $pivotRows = \Illuminate\Support\Facades\DB::connection('mongodb')
                    ->table('restaurant_user')
                    ->where('email', $user->email)
                    ->get();

                $restaurantIds = collect($pivotRows)->pluck('restaurant_id')->toArray();
                $restaurantNames = [];
                if (! empty($restaurantIds)) {
                    $restaurantNames = \App\Models\Restaurant::whereIn('id', $restaurantIds)
                        ->pluck('name')
                        ->toArray();
                }

                $roles = collect($pivotRows)->pluck('role')->filter()->unique()->toArray();

                fputcsv($file, [
                    $user->name,
                    $user->email,
                    $user->phone,
                    $user->created_at->format('Y-m-d'),
                    $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : 'Never',
                    implode(', ', $restaurantNames),
                    implode(', ', $roles),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=users_export_'.date('Y-m-d').'.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
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
