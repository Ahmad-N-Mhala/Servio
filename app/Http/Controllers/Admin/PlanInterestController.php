<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlanInterest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlanInterestController extends Controller
{
    public function index(Request $request)
    {
        $query = PlanInterest::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('restaurant_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $interests = $query->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/PlanInterests/Index', [
            'interests' => $interests,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function update(Request $request, PlanInterest $planInterest)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,not_started,in_progress,subscribed,not_subscribed',
            'admin_notes' => 'nullable|string',
        ]);

        $planInterest->update($validated);

        return back()->with('success', 'Plan interest updated successfully.');
    }

    public function export(Request $request)
    {
        $filename = 'plan-interests-' . date('Y-m-d-His') . '.csv';

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['ID', 'Date', 'Name', 'Email', 'Phone', 'Restaurant', 'Plan', 'Status', 'Message', 'Admin Notes'];

        $callback = function () use ($request, $columns) {
            $file = fopen('php://output', 'w');
            // Add BOM for Excel compatibility
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, $columns);

            $query = PlanInterest::query()->latest();

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('restaurant_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }

            // Use cursor for MongoDB efficiency instead of chunk if possible, or simple get for now since it's admin
            // MongoDB driver sometimes has issues with chunking without specific ordering ID in some older versions, but latest() should work.
            // Using cursor() is safer with streams.
            foreach ($query->cursor() as $interest) {
                fputcsv($file, [
                    $interest->id,
                    $interest->created_at->format('Y-m-d H:i:s'),
                    $interest->name,
                    $interest->email,
                    $interest->phone,
                    $interest->restaurant_name,
                    $interest->plan_name,
                    ucfirst(str_replace('_', ' ', $interest->status)),
                    $interest->message,
                    $interest->admin_notes
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
