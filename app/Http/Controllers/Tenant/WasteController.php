<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\WasteLog;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WasteController extends Controller
{
    public function index(Request $request)
    {
        $restaurant = \App\Models\Restaurant::find(session('active_restaurant_id')) ?? \App\Models\Restaurant::first();

        $query = WasteLog::where('restaurant_id', $restaurant->id)
            ->with(['menuItem']);

        if ($request->filled('date')) {
            $query->whereDate('log_date', $request->date);
        } else {
            // Default to today? Or show recent?
            // Let's show recent, but filtering is better.
            // $query->whereDate('log_date', today());
        }

        $logs = $query->orderBy('log_date', 'desc')->paginate(20);

        $menuItems = MenuItem::where('restaurant_id', $restaurant->id)
            ->where('is_available', true)
            ->select('id', 'name', 'price')
            ->get();

        return Inertia::render('Waste/Index', [
            'logs' => $logs,
            'menuItems' => $menuItems,
            'filters' => $request->only(['date'])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'menu_item_id' => 'required|exists:menu_items,id',
            'added_amount' => 'required|integer|min:0',
            'log_date' => 'required|date',
        ]);

        $restaurant = \App\Models\Restaurant::find(session('active_restaurant_id')) ?? \App\Models\Restaurant::first();
        $item = MenuItem::find($validated['menu_item_id']);

        WasteLog::create([
            'restaurant_id' => $restaurant->id,
            'menu_item_id' => $validated['menu_item_id'],
            'log_date' => $validated['log_date'],
            'added_amount' => $validated['added_amount'],
            'waste_amount' => 0,
            'cost_per_unit' => $item->price,
            'total_loss' => 0,
        ]);

        return redirect()->back()->with('message', 'Production log created.');
    }

    public function update(Request $request, WasteLog $wasteLog)
    {
        $validated = $request->validate([
            'waste_amount' => 'required|integer|min:0',
        ]);

        $loss = $validated['waste_amount'] * $wasteLog->cost_per_unit;

        $wasteLog->update([
            'waste_amount' => $validated['waste_amount'],
            'total_loss' => $loss
        ]);

        return redirect()->back()->with('message', 'Waste updated.');
    }
}
