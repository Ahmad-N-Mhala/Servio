<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\CommunicationBundle;
use App\Models\CommunicationLog;
use App\Models\CommunicationTemplate;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CommunicationController extends Controller
{
    public function index(Request $request): Response
    {
        $restaurant = Restaurant::find(session('active_restaurant_id'));
        if (!$restaurant)
            abort(404, 'Restaurant context not found');

        // Ensure default bundles exist
        if (CommunicationBundle::count() === 0) {
            $this->seedBundles();
        }

        // --- Logs Query ---
        $logsQuery = CommunicationLog::query()->with('template');

        if ($request->filled('type')) {
            $logsQuery->where('type', $request->input('type'));
        }
        if ($request->filled('status')) {
            $logsQuery->where('status', $request->input('status'));
        }
        if ($request->filled('search')) {
            $search = $request->input('search');
            $logsQuery->where(function ($q) use ($search) {
                $q->where('recipient', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }
        if ($request->filled('date_from')) {
            $logsQuery->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $logsQuery->whereDate('created_at', '<=', $request->input('date_to'));
        }
        if ($request->filled('template_id')) {
            $logsQuery->where('communication_template_id', $request->input('template_id'));
        }

        $logs = $logsQuery->latest()
            ->paginate(10)
            ->withQueryString();

        // --- Templates Query ---
        $templates = CommunicationTemplate::where('restaurant_id', $restaurant->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($template) {
                // Manually count logs to avoid MongoDB withCount limitations
                $template->logs_count = CommunicationLog::where('communication_template_id', $template->id)->count();
                return $template;
            });

        $bundles = CommunicationBundle::where('is_active', true)->get();

        return Inertia::render('Communication/Index', [
            'balances' => [
                'sms' => $restaurant->sms_balance ?? 0,
                'email' => $restaurant->email_balance ?? 0,
            ],
            'logs' => $logs,
            'bundles' => $bundles,
            'templates' => $templates,
            'filters' => $request->only(['type', 'status', 'search', 'date_from', 'date_to', 'template_id', 'active_tab']),
        ]);
    }

    public function purchaseBundle(Request $request, CommunicationBundle $bundle)
    {
        $restaurant = Restaurant::find(session('active_restaurant_id'));
        if (!$restaurant)
            abort(404, 'Restaurant context not found');

        if ($bundle->type === 'sms') {
            $restaurant->increment('sms_balance', $bundle->quantity);
        } else {
            $restaurant->increment('email_balance', $bundle->quantity);
        }

        return redirect()->back()->with('message', "Purchased {$bundle->name} successfully! Added {$bundle->quantity} {$bundle->type} credits.");
    }

    public function showTemplateLogs(Request $request, CommunicationTemplate $template)
    {
        // Redirect to index with filtered logs
        return redirect()->route('communication.index', [
            'template_id' => $template->id,
            'active_tab' => 'logs' // We will handle this in frontend to open logs tab
        ]);
    }

    public function storeTemplate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'channels' => 'required|array',
            'channels.*' => 'in:sms,email',
            'trigger_event' => 'required|string',
            'subject_en' => 'nullable|string|max:255',
            'subject_ar' => 'nullable|string|max:255',
            'content_en' => 'nullable|string',
            'content_ar' => 'nullable|string',
            'sms_content_en' => 'nullable|string|max:160',
            'sms_content_ar' => 'nullable|string|max:160',
            'conditions' => 'nullable|array',
            'is_active' => 'boolean',
            'timing_type' => 'required|in:immediately,before,after',
            'timing_days' => 'required_if:timing_type,before,after|integer|min:0',
            'timing_time' => 'required|date_format:H:i',
        ]);

        $restaurant = Restaurant::find(session('active_restaurant_id'));
        if (!$restaurant)
            abort(404, 'Restaurant context not found');

        CommunicationTemplate::create(array_merge($validated, [
            'restaurant_id' => $restaurant->id,
            'conditions' => $validated['conditions'] ?? [],
            'timing_days' => $validated['timing_days'] ?? 0,
        ]));

        return redirect()->back()->with('message', 'Communication rule created successfully.');
    }

    public function updateTemplate(Request $request, CommunicationTemplate $template)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'channels' => 'required|array',
            'channels.*' => 'in:sms,email',
            'trigger_event' => 'required|string',
            'subject_en' => 'nullable|string|max:255',
            'subject_ar' => 'nullable|string|max:255',
            'content_en' => 'nullable|string',
            'content_ar' => 'nullable|string',
            'sms_content_en' => 'nullable|string|max:160',
            'sms_content_ar' => 'nullable|string|max:160',
            'conditions' => 'nullable|array',
            'is_active' => 'boolean',
            'timing_type' => 'required|in:immediately,before,after',
            'timing_days' => 'required_if:timing_type,before,after|integer|min:0',
            'timing_time' => 'required|date_format:H:i',
        ]);

        $template->update(array_merge($validated, [
            'timing_days' => $validated['timing_days'] ?? 0,
        ]));

        return redirect()->back()->with('message', 'Communication rule updated successfully.');
    }

    public function destroyTemplate(CommunicationTemplate $template)
    {
        $template->delete();
        return redirect()->back()->with('message', 'Communication rule deleted successfully.');
    }

    private function seedBundles()
    {
        $defaults = [
            ['name' => 'Starter SMS Pack', 'type' => 'sms', 'quantity' => 100, 'price' => 50.00],
            ['name' => 'Pro SMS Pack', 'type' => 'sms', 'quantity' => 500, 'price' => 200.00],
            ['name' => 'Starter Email Pack', 'type' => 'email', 'quantity' => 1000, 'price' => 100.00],
            ['name' => 'Pro Email Pack', 'type' => 'email', 'quantity' => 5000, 'price' => 400.00],
        ];
        foreach ($defaults as $bundle) {
            CommunicationBundle::create($bundle);
        }
    }
}
