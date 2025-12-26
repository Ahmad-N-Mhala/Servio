<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\Table;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TableController extends Controller
{
    public function index()
    {
        \Illuminate\Support\Facades\Gate::authorize('view_tables');
        $restaurant = Restaurant::find(session('active_restaurant_id')) ?? Restaurant::first();
        $tables = Table::where('restaurant_id', $restaurant->id)
            ->orderBy('name')
            ->get();

        return Inertia::render('Tables/Index', [
            'tables' => $tables,
        ]);
    }

    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('create_table');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|in:available,occupied,reserved',
            'location' => 'nullable|string|max:255',
        ]);

        $restaurant = Restaurant::find(session('active_restaurant_id')) ?? Restaurant::first();

        Table::create([
            'restaurant_id' => $restaurant->id,
            'name' => $validated['name'],
            'capacity' => $validated['capacity'],
            'status' => $validated['status'],
            'location' => $validated['location'],
        ]);

        return redirect()->back()->with('message', 'Table created successfully');
    }

    public function update(Request $request, Table $table)
    {
        \Illuminate\Support\Facades\Gate::authorize('edit_table');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|in:available,occupied,reserved',
            'location' => 'nullable|string|max:255',
        ]);

        $table->update($validated);

        return redirect()->back()->with('message', 'Table updated successfully');
    }

    public function destroy(Table $table)
    {
        \Illuminate\Support\Facades\Gate::authorize('delete_table');
        $table->delete();
        return redirect()->back()->with('message', 'Table deleted successfully');
    }
}
