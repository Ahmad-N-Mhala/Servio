<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\MonthlyExpense;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MonthlyExpenseController extends Controller
{
    public function index(Request $request)
    {
        $restaurant = $request->user()->currentRestaurant();

        // Enforce strict context - removed implicit fallback for super admin
        if (! $restaurant && $request->user()->is_super_admin && session('active_restaurant_id')) {
            $restaurant = auth()->user()->currentRestaurant();
        }

        if (! $restaurant) {
            abort(404, 'Restaurant context not found');
        }

        // Get selected month and year, default to current
        $selectedMonthStr = $request->input('month', now()->format('Y-m'));

        // Calculate Inventory Purchases for the month
        $monthStart = \Carbon\Carbon::parse($selectedMonthStr.'-01')->startOfMonth();
        $monthEnd = \Carbon\Carbon::parse($selectedMonthStr.'-01')->endOfMonth();

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

        // Get all expenses for the selected month
        $expenses = MonthlyExpense::where('restaurant_id', $restaurant->id)
            ->where('month', $selectedMonthStr)
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
                    'evidence_files' => $expense->evidence_files ?? [],
                ];
            });

        // Calculate totals including inventory purchases
        $totalExpenses = $expenses->sum('amount') + $inventoryPurchases;
        $paidExpenses = $expenses->where('payment_status', 'paid')->sum('amount') + $inventoryPurchases;
        $pendingExpenses = $expenses->where('payment_status', 'pending')->sum('amount');

        if ($request->has('export') && $request->export === 'excel') {
            return $this->exportToExcel($expenses, $inventoryPurchases, $selectedMonthStr);
        }

        return Inertia::render('MonthlyExpenses/Index', [
            'expenses' => $expenses,
            'selectedMonth' => $selectedMonthStr,
            'summary' => [
                'total' => $totalExpenses,
                'paid' => $paidExpenses,
                'pending' => $pendingExpenses,
            ],
            'inventoryPurchases' => $inventoryPurchases,
            'categories' => $this->getExpenseCategories(),
        ]);
    }

    private function exportToExcel($expenses, $inventoryPurchases, $month)
    {
        $csvData = [];
        $csvData[] = ['Category', 'Description', 'Amount', 'Status', 'Paid Date', 'Notes'];

        // Add inventory purchases as first row
        if ($inventoryPurchases > 0) {
            $csvData[] = [
                'Inventory Purchases (Auto-calculated)',
                'Automated sum of received purchase orders and ingredient stocks.',
                round($inventoryPurchases, 2),
                'paid',
                '-',
                '',
            ];
        }

        foreach ($expenses as $exp) {
            $csvData[] = [
                $exp['category'],
                $exp['description'] ?? '',
                round($exp['amount'], 2),
                $exp['payment_status'],
                $exp['paid_at'] ?? '-',
                $exp['notes'] ?? '',
            ];
        }

        $filename = "monthly_expenses_{$month}.csv";
        $handle = fopen('php://temp', 'r+');
        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"$filename\"");
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
            'evidence_files' => ['nullable', 'array'],
            'evidence_files.*' => ['file', 'max:5120'], // 5MB
        ]);

        $validated['restaurant_id'] = $restaurant->id;
        $validated['created_by'] = $request->user()->id;

        $evidencePaths = [];
        if ($request->hasFile('evidence_files')) {
            foreach ($request->file('evidence_files') as $file) {
                $path = $file->store('monthly_expenses', 'public');
                $evidencePaths[] = [
                    'name' => $file->getClientOriginalName(),
                    'url' => '/storage/'.$path,
                ];
            }
        }
        $validated['evidence_files'] = $evidencePaths;

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
            'evidence_files' => ['nullable', 'array'],
            'evidence_files.*' => ['file', 'max:5120'],
        ]);

        if ($request->hasFile('evidence_files')) {
            $evidencePaths = [];
            foreach ($request->file('evidence_files') as $file) {
                $path = $file->store('monthly_expenses', 'public');
                $evidencePaths[] = [
                    'name' => $file->getClientOriginalName(),
                    'url' => '/storage/'.$path,
                ];
            }
            $existing = $monthlyExpense->evidence_files ?? [];
            $validated['evidence_files'] = array_merge($existing, $evidencePaths);
        } else {
            unset($validated['evidence_files']);
        }

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
