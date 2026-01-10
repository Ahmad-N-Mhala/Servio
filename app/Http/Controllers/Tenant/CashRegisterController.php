<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\CashRegister;
use App\Models\CashTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class CashRegisterController extends Controller
{
    /**
     * Display cash register page
     */
    public function index(Request $request): Response
    {
        $restaurant = $request->user()->currentRestaurant();

        if (!$restaurant && $request->user()->is_super_admin) {
            $restaurant = \App\Models\Restaurant::orderBy('created_at', 'desc')->first();
        }

        // Get current open register for this user
        $currentRegister = CashRegister::where('restaurant_id', $restaurant->id)
            ->where('user_id', $request->user()->id)
            ->where('status', 'open')
            ->with([
                'transactions' => function ($query) {
                    $query->latest()->limit(50);
                }
            ])
            ->first();

        // Get recent closed registers
        $recentRegisters = CashRegister::where('restaurant_id', $restaurant->id)
            ->where('status', 'closed')
            ->with('user')
            ->latest('closed_at')
            ->limit(10)
            ->get();

        return Inertia::render('CashRegister/Index', [
            'currentRegister' => $currentRegister,
            'currentBalance' => $currentRegister ? $currentRegister->getCurrentBalance() : 0,
            'recentRegisters' => $recentRegisters,
        ]);
    }

    /**
     * Open a new cash register
     */
    public function open(Request $request)
    {
        $validated = $request->validate([
            'opening_balance' => 'required|numeric|min:0',
            'opening_notes' => 'nullable|string|max:500',
        ]);

        $restaurant = $request->user()->currentRestaurant();

        if (!$restaurant && $request->user()->is_super_admin) {
            $restaurant = \App\Models\Restaurant::orderBy('created_at', 'desc')->first();
        }

        // Check if user already has an open register
        $existingRegister = CashRegister::where('restaurant_id', $restaurant->id)
            ->where('user_id', $request->user()->id)
            ->where('status', 'open')
            ->first();

        if ($existingRegister) {
            return redirect()->back()->withErrors([
                'error' => 'You already have an open cash register. Please close it first.'
            ]);
        }

        // Create new register
        $register = CashRegister::create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $request->user()->id,
            'opening_balance' => $validated['opening_balance'],
            'opened_at' => now(),
            'status' => 'open',
            'opening_notes' => $validated['opening_notes'] ?? null,
        ]);

        // Create opening transaction
        CashTransaction::create([
            'cash_register_id' => $register->id,
            'restaurant_id' => $restaurant->id,
            'user_id' => $request->user()->id,
            'type' => 'opening',
            'amount' => $validated['opening_balance'],
            'balance_after' => $validated['opening_balance'],
            'notes' => 'Cash register opened',
        ]);

        return redirect()->back()->with('success', 'Cash register opened successfully.');
    }

    /**
     * Close the cash register
     */
    public function close(Request $request, CashRegister $cashRegister)
    {
        $validated = $request->validate([
            'closing_balance' => 'required|numeric|min:0',
            'closing_notes' => 'nullable|string|max:500',
        ]);

        $restaurant = $request->user()->currentRestaurant();

        // Verify ownership
        if ($cashRegister->user_id !== $request->user()->id) {
            abort(403, 'You can only close your own cash register.');
        }

        if ($cashRegister->status === 'closed') {
            return redirect()->back()->withErrors([
                'error' => 'This cash register is already closed.'
            ]);
        }

        $expectedBalance = $cashRegister->getCurrentBalance();
        $difference = $validated['closing_balance'] - $expectedBalance;

        $cashRegister->update([
            'closing_balance' => $validated['closing_balance'],
            'expected_balance' => $expectedBalance,
            'difference' => $difference,
            'closed_at' => now(),
            'status' => 'closed',
            'closing_notes' => $validated['closing_notes'] ?? null,
        ]);

        // Create closing transaction
        CashTransaction::create([
            'cash_register_id' => $cashRegister->id,
            'restaurant_id' => $restaurant->id,
            'user_id' => $request->user()->id,
            'type' => 'closing',
            'amount' => 0,
            'balance_after' => $validated['closing_balance'],
            'notes' => 'Cash register closed',
        ]);

        return redirect()->back()->with('success', 'Cash register closed successfully.');
    }

    /**
     * Add cash withdrawal
     */
    public function withdraw(Request $request, CashRegister $cashRegister)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'required|string|max:500',
        ]);

        // Verify ownership and status
        if ($cashRegister->user_id !== $request->user()->id) {
            abort(403, 'You can only withdraw from your own cash register.');
        }

        if ($cashRegister->status !== 'open') {
            return redirect()->back()->withErrors([
                'error' => 'Cash register must be open to withdraw cash.'
            ]);
        }

        $currentBalance = $cashRegister->getCurrentBalance();

        if ($validated['amount'] > $currentBalance) {
            return redirect()->back()->withErrors([
                'error' => 'Insufficient cash in register.'
            ]);
        }

        $newBalance = $currentBalance - $validated['amount'];

        // Create withdrawal transaction
        CashTransaction::create([
            'cash_register_id' => $cashRegister->id,
            'restaurant_id' => $cashRegister->restaurant_id,
            'user_id' => $request->user()->id,
            'type' => 'withdrawal',
            'amount' => -$validated['amount'],
            'balance_after' => $newBalance,
            'notes' => $validated['notes'],
        ]);

        return redirect()->back()->with('success', 'Cash withdrawn successfully.');
    }

    /**
     * Add cash deposit
     */
    public function deposit(Request $request, CashRegister $cashRegister)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'required|string|max:500',
        ]);

        // Verify ownership and status
        if ($cashRegister->user_id !== $request->user()->id) {
            abort(403, 'You can only deposit to your own cash register.');
        }

        if ($cashRegister->status !== 'open') {
            return redirect()->back()->withErrors([
                'error' => 'Cash register must be open to deposit cash.'
            ]);
        }

        $currentBalance = $cashRegister->getCurrentBalance();
        $newBalance = $currentBalance + $validated['amount'];

        // Create deposit transaction
        CashTransaction::create([
            'cash_register_id' => $cashRegister->id,
            'restaurant_id' => $cashRegister->restaurant_id,
            'user_id' => $request->user()->id,
            'type' => 'deposit',
            'amount' => $validated['amount'],
            'balance_after' => $newBalance,
            'notes' => $validated['notes'],
        ]);

        return redirect()->back()->with('success', 'Cash deposited successfully.');
    }

    /**
     * Record a cash sale (called from POS)
     */
    public function recordSale(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $restaurant = $request->user()->currentRestaurant();

        // Get current open register for this user
        $cashRegister = CashRegister::where('restaurant_id', $restaurant->id)
            ->where('user_id', $request->user()->id)
            ->where('status', 'open')
            ->first();

        if (!$cashRegister) {
            return response()->json([
                'error' => 'No open cash register found. Please open the cash register first.'
            ], 400);
        }

        $currentBalance = $cashRegister->getCurrentBalance();
        $newBalance = $currentBalance + $validated['amount'];

        // Create sale transaction
        CashTransaction::create([
            'cash_register_id' => $cashRegister->id,
            'restaurant_id' => $restaurant->id,
            'user_id' => $request->user()->id,
            'order_id' => $validated['order_id'],
            'type' => 'sale',
            'amount' => $validated['amount'],
            'balance_after' => $newBalance,
            'notes' => 'Cash payment for order #' . $validated['order_id'],
        ]);

        return response()->json([
            'success' => true,
            'new_balance' => $newBalance,
        ]);
    }

    /**
     * Display cash register history
     */
    public function history(Request $request): Response
    {
        $restaurant = $request->user()->currentRestaurant();

        if (!$restaurant && $request->user()->is_super_admin) {
            $restaurant = \App\Models\Restaurant::orderBy('created_at', 'desc')->first();
        }

        // Get filters
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        // $cashierId = $request->input('cashier_id'); // Removing cashier filter as requested

        // Build query
        $query = CashRegister::where('restaurant_id', $restaurant->id)
            ->with([
                'user',
                'transactions' => function ($q) {
                    $q->orderBy('created_at', 'asc')->with('order');
                }
            ]);

        // Apply filters
        if ($startDate) {
            $query->where('opened_at', '>=', Carbon::parse($startDate)->startOfDay());
        }

        if ($endDate) {
            $query->where('opened_at', '<=', Carbon::parse($endDate)->endOfDay());
        }

        /* Removing cashier filter
        if ($cashierId) {
            $query->where('user_id', $cashierId);
        }
        */

        // Get registers with pagination
        $registers = $query->latest('opened_at')
            ->paginate(20)
            ->withQueryString();

        // Get all cashiers for filter dropdown
        $cashiers = \App\Models\User::whereHas('restaurants', function ($q) use ($restaurant) {
            $q->where('restaurant_id', $restaurant->id);
        })->get(['id', 'name']);

        return Inertia::render('CashRegister/History', [
            'registers' => $registers,
            'cashiers' => $cashiers,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                // 'cashier_id' => $cashierId,
            ],
        ]);
    }

    /**
     * Export cash register details to CSV
     */
    public function export(CashRegister $cashRegister)
    {
        // Eager load transactions
        $cashRegister->load([
            'user',
            'transactions' => function ($q) {
                $q->orderBy('created_at', 'asc');
            }
        ]);

        $filename = 'cash_register_' . $cashRegister->opened_at->format('Y-m-d') . '.csv';

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($cashRegister) {
            $file = fopen('php://output', 'w');

            // Add BOM for Excel UTF-8 compatibility
            fputs($file, "\xEF\xBB\xBF");

            // Title
            fputcsv($file, [__('reports.cash_register_report')]);
            fputcsv($file, []); // Spacer

            // 1. General Info Section - Horizontal Headers
            fputcsv($file, [__('reports.general_information')]);
            fputcsv($file, [__('reports.restaurant'), __('reports.cashier'), __('reports.status'), __('reports.opened_at'), __('reports.closed_at')]);
            fputcsv($file, [
                $cashRegister->restaurant->name ?? 'N/A',
                $cashRegister->user->name,
                strtoupper($cashRegister->status),
                $cashRegister->opened_at->format('Y-m-d H:i:s'),
                $cashRegister->closed_at ? $cashRegister->closed_at->format('Y-m-d H:i:s') : 'N/A'
            ]);
            fputcsv($file, []); // Spacer

            // 2. Financial Summary Section - Horizontal Headers
            fputcsv($file, [__('reports.financial_summary')]);
            $financialHeaders = [__('reports.opening_balance')];
            $financialValues = [number_format((float) $cashRegister->opening_balance, 2)];

            if ($cashRegister->status === 'closed') {
                $financialHeaders[] = __('reports.expected_balance');
                $financialHeaders[] = __('reports.actual_closing_balance');
                $financialHeaders[] = __('reports.difference');

                $financialValues[] = number_format((float) $cashRegister->expected_balance, 2);
                $financialValues[] = number_format((float) $cashRegister->closing_balance, 2);
                $financialValues[] = number_format((float) $cashRegister->difference, 2);
            }

            fputcsv($file, $financialHeaders);
            fputcsv($file, $financialValues);
            fputcsv($file, []); // Spacer

            // 3. Notes Section
            if ($cashRegister->opening_notes || $cashRegister->closing_notes) {
                fputcsv($file, [strtoupper(__('reports.notes'))]);
                fputcsv($file, [__('reports.type'), __('reports.content')]);
                if ($cashRegister->opening_notes)
                    fputcsv($file, [__('reports.opening_notes'), $cashRegister->opening_notes]);
                if ($cashRegister->closing_notes)
                    fputcsv($file, [__('reports.closing_notes'), $cashRegister->closing_notes]);
                fputcsv($file, []); // Spacer
            }

            // 4. Transactions Section
            fputcsv($file, [__('reports.transaction_history_cash')]);
            fputcsv($file, [__('reports.time'), __('reports.type'), __('reports.amount'), __('reports.balance_after'), __('reports.notes')]);

            // Transactions Data
            foreach ($cashRegister->transactions as $transaction) {
                fputcsv($file, [
                    $transaction->created_at->format('H:i:s'),
                    strtoupper($transaction->type),
                    number_format((float) $transaction->amount, 2),
                    number_format((float) $transaction->balance_after, 2),
                    $transaction->notes
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
