<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\CommunicationBundle;
use App\Models\CommunicationLog;
use App\Models\CommunicationTemplate;
use App\Models\MenuCategory;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CommunicationController extends Controller
{
    public function index(Request $request): Response
    {
        $restaurant = auth()->user()->currentRestaurant();
        if (!$restaurant && auth()->user()->is_super_admin && $request->has('restaurant_id')) {
            $restaurant = \App\Models\Restaurant::find($request->input('restaurant_id'));
        }

        if (!$restaurant)
            abort(404, 'Restaurant context not found');

        // Ensure default bundles exist
        if (CommunicationBundle::count() === 0) {
            $this->seedBundles();
        }

        // --- Logs Query ---
        $logsQuery = CommunicationLog::query()->where('restaurant_id', (string) $restaurant->id)->with('template');

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

        // Calculate sent counts based on the FILTERED query (before pagination)
        $smsSentCount = (clone $logsQuery)->where('type', 'sms')->whereIn('status', ['sent', 'simulated'])->count();
        $emailSentCount = (clone $logsQuery)->where('type', 'email')->whereIn('status', ['sent', 'simulated'])->count();

        $logs = $logsQuery->latest()
            ->paginate(10)
            ->withQueryString();

        // --- Templates Query ---
        $templates = CommunicationTemplate::where('restaurant_id', $restaurant->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($template) {
                // Manually count logs to avoid MongoDB withCount limitations
                $template->logs_count = CommunicationLog::where('communication_template_id', (string) $template->id)->count();
                return $template;
            });

        $bundles = CommunicationBundle::where('is_active', true)->get();

        $menuCategories = MenuCategory::where('restaurant_id', $restaurant->id)
            ->where('is_active', true)
            ->with([
                'items'
            ])
            ->get();

        return Inertia::render('Communication/Index', [
            'balances' => [
                'sms_sent' => $smsSentCount,
                'email_sent' => $emailSentCount,
            ],
            'menuCategories' => $menuCategories,
            'logs' => $logs,
            'bundles' => $bundles,
            'templates' => $templates,
            'filters' => $request->only(['type', 'status', 'search', 'date_from', 'date_to', 'template_id', 'active_tab']),
        ]);
    }

    public function purchaseBundle(Request $request, CommunicationBundle $bundle)
    {
        $restaurant = auth()->user()->currentRestaurant();
        if (!$restaurant && auth()->user()->is_super_admin && $request->has('restaurant_id')) {
            $restaurant = \App\Models\Restaurant::find($request->input('restaurant_id'));
        }

        if (!$restaurant)
            abort(404, 'Restaurant context not found');

        // Balances are now unlimited and not tracked per restaurant.
        // if ($bundle->type === 'sms') {
        //     $restaurant->increment('sms_balance', $bundle->quantity);
        // } else {
        //     $restaurant->increment('email_balance', $bundle->quantity);
        // }

        return redirect()->back()->with('message', "Purchased {$bundle->name} successfully! (Unlimited Model)");
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
        $restaurant = auth()->user()->currentRestaurant();
        if (!$restaurant && auth()->user()->is_super_admin && $request->has('restaurant_id')) {
            $restaurant = \App\Models\Restaurant::find($request->input('restaurant_id'));
        }

        if (!$restaurant)
            abort(404, 'Restaurant context not found');

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
            'reward_config' => 'nullable|array',
            'reward_config.reward_type' => 'required_with:reward_config|in:discount_percentage,discount_fixed,free_item,cashback',
            'reward_config.points_required' => 'nullable|numeric|min:0',
            'reward_config.discount_value' => 'nullable|numeric|min:0',
            'reward_config.min_order_value' => 'nullable|numeric|min:0',
            'reward_config.apply_on' => 'nullable|string|in:all,specific',
            'reward_config.menu_item_ids' => 'nullable|array',
            'reward_config.name' => 'required_with:reward_config|array',
            'reward_config.name.en' => 'required_with:reward_config|string',
            'reward_config.name.ar' => 'nullable|string',
            'reward_config.description' => 'nullable|string',
        ]);

        CommunicationTemplate::create(array_merge($validated, [
            'restaurant_id' => $restaurant->id,
            'conditions' => $validated['conditions'] ?? [],
            'timing_days' => $validated['timing_days'] ?? 0,
            'reward_config' => $validated['reward_config'] ?? null,
        ]));

        return redirect()->back()->with('message', 'Communication rule created successfully.');
    }

    public function updateTemplate(Request $request, CommunicationTemplate $template)
    {
        $restaurant = auth()->user()->currentRestaurant();
        if (!$restaurant && auth()->user()->is_super_admin) {
            $restaurant = \App\Models\Restaurant::find($template->restaurant_id);
        }

        if (!$restaurant || (!auth()->user()->is_super_admin && $template->restaurant_id !== $restaurant->id)) {
            abort(403, 'Unauthorized action.');
        }

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
            'reward_config' => 'nullable|array',
            'reward_config.reward_type' => 'required_with:reward_config|in:discount_percentage,discount_fixed,free_item,cashback',
            'reward_config.points_required' => 'nullable|numeric|min:0',
            'reward_config.discount_value' => 'nullable|numeric|min:0',
            'reward_config.min_order_value' => 'nullable|numeric|min:0',
            'reward_config.apply_on' => 'nullable|string|in:all,specific',
            'reward_config.menu_item_ids' => 'nullable|array',
            'reward_config.name' => 'required_with:reward_config|array',
            'reward_config.name.en' => 'required_with:reward_config|string',
            'reward_config.name.ar' => 'nullable|string',
            'reward_config.description' => 'nullable|string',
        ]);

        $template->update(array_merge($validated, [
            'timing_days' => $validated['timing_days'] ?? 0,
            'reward_config' => $validated['reward_config'] ?? null,
        ]));

        return redirect()->back()->with('message', 'Communication rule updated successfully.');
    }

    public function destroyTemplate(CommunicationTemplate $template)
    {
        $restaurant = auth()->user()->currentRestaurant();
        if (!$restaurant && auth()->user()->is_super_admin) {
            $restaurant = \App\Models\Restaurant::find($template->restaurant_id);
        }

        if (!$restaurant || (!auth()->user()->is_super_admin && $template->restaurant_id !== $restaurant->id)) {
            abort(403, 'Unauthorized action.');
        }

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
