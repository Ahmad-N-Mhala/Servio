<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\MonthlyExpense;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MonthlyExpenseController extends Controller
{
    public function index(Request $request): Response
    {
        $restaurant = $request->user()->currentRestaurant();

        // Enforce strict context - removed implicit fallback for super admin
        if (!$restaurant && $request->user()->is_super_admin && session('active_restaurant_id')) {
            $restaurant = \App\Models\Restaurant::find(session('active_restaurant_id'));
        }

        if (!$restaurant) {
            abort(404, 'Restaurant context not found');
        }

        // Get selected month or default to current month
        $selectedMonth = $request->input('month', now()->format('Y-m'));

        // Get all expenses for the selected month
        $expenses = MonthlyExpense::where('restaurant_id', $restaurant->id)
            ->where('month', $selectedMonth)
            ->orderBy('category')
            ->get()
            ->map(function ($expense) {
                return [
                    'id' => $expense->id,
                    'category' => $expense->category,
                    'description' => $expense->description,
                    'amount' => (float) (string) $expense->amount,
                    'payment_status' => $expense->payment_status,
                    'paid_at' => $expense->paid_at?->format('Y-m-d'),
                    'notes' => $expense->notes,
                ];
            });

        // Calculate totals
        $totalExpenses = $expenses->sum('amount');
        $paidExpenses = $expenses->where('payment_status', 'paid')->sum('amount');
        $pendingExpenses = $expenses->where('payment_status', 'pending')->sum('amount');

        // Calculate Inventory Purchases for the month
        // Get the start and end of the selected month
        $monthStart = \Carbon\Carbon::parse($selectedMonth . '-01')->startOfMonth();
        $monthEnd = \Carbon\Carbon::parse($selectedMonth . '-01')->endOfMonth();

        // Approach 1: Sum from ingredient_batches created in this month
        $inventoryPurchases = \Illuminate\Support\Facades\DB::table('ingredient_batches')
            ->join('ingredients', 'ingredient_batches.ingredient_id', '=', 'ingredients.id')
            ->where('ingredients.restaurant_id', $restaurant->id)
            ->whereBetween('ingredient_batches.created_at', [$monthStart, $monthEnd])
            ->get()
            ->sum(function ($batch) {
                $qty = (float) (string) ($batch->quantity_initial ?? 0);
                $cost = (float) (string) ($batch->cost_per_unit ?? 0);
                return $qty * $cost;
            });

        // Fallback: If no batches, try to calculate from ingredients added this month
        if ($inventoryPurchases == 0) {
            $inventoryPurchases = \App\Models\Ingredient::where('restaurant_id', $restaurant->id)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->get()
                ->sum(function ($ingredient) {
                    $stock = (float) (string) ($ingredient->current_stock ?? 0);
                    $cost = (float) (string) ($ingredient->cost ?? 0);
                    return $stock * $cost;
                });
        }

        // Get available months (3 future months + current + 12 past months)
        $availableMonths = collect();
        for ($i = 3; $i >= -12; $i--) {
            $date = now()->addMonths($i);
            $availableMonths->push([
                'value' => $date->format('Y-m'),
                'label' => $date->format('F Y'),
            ]);
        }

        return Inertia::render('MonthlyExpenses/Index', [
            'expenses' => $expenses,
            'selectedMonth' => $selectedMonth,
            'availableMonths' => $availableMonths,
            'summary' => [
                'total' => $totalExpenses,
                'paid' => $paidExpenses,
                'pending' => $pendingExpenses,
            ],
            'inventoryPurchases' => $inventoryPurchases,
            'categories' => $this->getExpenseCategories(),
        ]);
    }

    public function store(Request $request)
    {
        $restaurant = $request->user()->currentRestaurant();

        $validated = $request->validate([
            'category' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'amount' => ['required', 'numeric', 'min:0'],
            'month' => ['required', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'payment_status' => ['required', 'in:pending,paid'],
            'paid_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['restaurant_id'] = $restaurant->id;
        $validated['created_by'] = $request->user()->id;

        MonthlyExpense::create($validated);

        return redirect()->back()->with('message', 'Expense added successfully.');
    }

    public function update(Request $request, MonthlyExpense $monthlyExpense)
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_status' => ['required', 'in:pending,paid'],
            'paid_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $monthlyExpense->update($validated);

        return redirect()->back()->with('message', 'Expense updated successfully.');
    }

    public function destroy(MonthlyExpense $monthlyExpense)
    {
        $monthlyExpense->delete();

        return redirect()->back()->with('message', 'Expense deleted successfully.');
    }

    private function getExpenseCategories(): array
    {
        return [
            'Rent',
            'Salaries',
            'Utilities',
            'Supplies',
            'Marketing',
            'Insurance',
            'Maintenance',
            'Equipment',
            'Licenses & Permits',
            'Other',
        ];
    }
}
