<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\Zone;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TableController extends Controller
{
    public function index()
    {
        \Illuminate\Support\Facades\Gate::authorize('view_tables');
        $restaurant = auth()->user()->currentRestaurant();
        if (! $restaurant) {
            abort(404, 'Restaurant context not found');
        }

        $zones = Zone::where('restaurant_id', $restaurant->id)
            ->with([
                'tables' => function ($query) {
                    $query->orderBy('name');
                },
            ])
            ->get();

        // Also get tables without a zone
        $orphanTables = Table::where('restaurant_id', $restaurant->id)
            ->whereNull('zone_id')
            ->orderBy('name')
            ->get();

        return Inertia::render('Tables/Index', [
            'zones' => $zones,
            'orphanTables' => $orphanTables,
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
            'zone_id' => 'nullable|exists:zones,id',
        ]);

        $restaurant = auth()->user()->currentRestaurant();
        if (! $restaurant) {
            abort(404, 'Restaurant context not found');
        }

        Table::create([
            'restaurant_id' => $restaurant->id,
            'zone_id' => $validated['zone_id'] ?? null,
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
            'zone_id' => 'nullable|exists:zones,id',
        ]);

        $table->update($validated);

        return redirect()->back()->with('message', 'Table updated successfully');
    }

    public function storeZone(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('create_table'); // Using create_table permission for now
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $restaurant = auth()->user()->currentRestaurant();

        Zone::create([
            'restaurant_id' => $restaurant->id,
            'name' => $validated['name'],
        ]);

        return redirect()->back()->with('message', 'Zone created successfully');
    }

    public function destroyZone(Zone $zone)
    {
        \Illuminate\Support\Facades\Gate::authorize('delete_table'); // Using delete_table permission for now

        // Check if zone belongs to current restaurant context (security)
        if ($zone->restaurant_id !== session('active_restaurant_id')) {
            abort(403);
        }

        $zone->delete();
        // Tables will set zone_id to null via DB constraint or just stay orphaned depending on MongoDB handling,
        // but let's explicity nullify if needed. MongoDB doesn't enforce cascade null on delete roughly speaking unless handled.

        return redirect()->back()->with('message', 'Zone deleted successfully');
    }

    public function destroy(Table $table)
    {
        \Illuminate\Support\Facades\Gate::authorize('delete_table');
        $table->delete();

        return redirect()->back()->with('message', 'Table deleted successfully');
    }

    /**
     * Regenerate QR code for a table
     */
    public function regenerateQrCode(Table $table)
    {
        \Illuminate\Support\Facades\Gate::authorize('edit_table');

        $table->regenerateQrCode();

        return redirect()->back()->with('message', 'QR code regenerated successfully');
    }
}
