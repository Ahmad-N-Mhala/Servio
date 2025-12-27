<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\Table;
use Illuminate\Http\Request;
use Inertia\Inertia;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TableController extends Controller
{
    public function index()
    {
        \Illuminate\Support\Facades\Gate::authorize('view_tables');
        $restaurant = Restaurant::find(session('active_restaurant_id')) ?? Restaurant::first();
        $tables = Table::where('restaurant_id', $restaurant->id)
            ->orderBy('name')
            ->get()
            ->map(function ($table) {
                return [
                    'id' => $table->id,
                    'name' => $table->name,
                    'capacity' => $table->capacity,
                    'status' => $table->status,
                    'location' => $table->location,
                    'qr_code_token' => $table->qr_code_token,
                    'qr_code_url' => $table->qr_code_url,
                ];
            });

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

    /**
     * Download QR code for a table
     */
    public function downloadQrCode(Table $table)
    {
        \Illuminate\Support\Facades\Gate::authorize('view_tables');

        $qrCode = QrCode::format('png')
            ->size(300)
            ->margin(2)
            ->errorCorrection('H')
            ->generate($table->qr_code_url);

        return response($qrCode)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="table-' . $table->name . '-qr.png"');
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
