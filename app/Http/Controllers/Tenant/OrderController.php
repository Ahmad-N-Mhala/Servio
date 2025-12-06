<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\LoyaltyService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(
        protected LoyaltyService $loyaltyService
    ) {
    }

    public function index(Request $request): Response
    {
        $restaurant = \App\Models\Restaurant::first();

        $query = Order::where('restaurant_id', $restaurant->id)
            ->with(['customer']);

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'ilike', "%{$search}%")
                    ->orWhere('customer_name', 'ilike', "%{$search}%")
                    ->orWhere('customer_phone', 'ilike', "%{$search}%")
                    ->orWhere('status', 'ilike', "%{$search}%")
                    ->orWhereRaw('CAST(total AS TEXT) ilike ?', ["%{$search}%"]);
            });
        }

        // Date Range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }

        // Sort
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        // Validate sort field to prevent SQL injection
        $allowedSorts = ['order_number', 'customer_name', 'total', 'status', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $orders = $query->paginate(10)
            ->withQueryString();

        return Inertia::render('Orders/Live', [
            'orders' => $orders,
            'currency' => $restaurant->currency ?? 'AED',
            'filters' => $request->only(['search', 'sort_field', 'sort_direction', 'start_date', 'end_date']),
        ]);
    }

    public function create(): Response
    {
        $restaurant = \App\Models\Restaurant::first();

        // Get available tables
        $tables = \App\Models\Table::where('restaurant_id', $restaurant->id)
            ->where('status', '!=', 'occupied') // Optional: only show available tables? Or allow any? Let's allow all for now but maybe indicate status
            ->orderBy('name')
            ->get();

        // Get menu categories with items for order creation
        $menuCategories = \App\Models\MenuCategory::where('restaurant_id', $restaurant->id)
            ->where('is_active', true)
            ->with([
                'items' => function ($query) {
                    $query->where('is_available', true)->orderByRaw("name->>'en' ASC");
                }
            ])
            ->orderBy('sort_order')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'items' => $category->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'name' => $item->name,
                            'price' => (float) $item->price,
                        ];
                    }),
                ];
            });

        // Get customers with loyalty points for selection
        $customers = \App\Models\Customer::where('restaurant_id', $restaurant->id)
            ->where('is_active', true)
            ->with('loyaltyPoints')
            ->orderBy('name')
            ->limit(100)
            ->get()
            ->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'loyalty_points' => $customer->loyaltyPoints?->balance ?? 0,
                ];
            });

        // Get active rewards for redemption
        $rewards = \App\Models\Reward::where('restaurant_id', $restaurant->id)
            ->where('is_active', true)
            ->orderBy('points_required')
            ->get()
            ->map(function ($reward) {
                return [
                    'id' => $reward->id,
                    'name' => $reward->name,
                    'description' => $reward->description,
                    'points_required' => $reward->points_required,
                    'reward_type' => $reward->reward_type,
                    'discount_value' => $reward->discount_value,
                ];
            });

        return Inertia::render('Orders/Create', [
            'menuCategories' => $menuCategories,
            'customers' => $customers,
            'rewards' => $rewards,
            'tables' => $tables,
            'currency' => $restaurant->currency ?? 'AED',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_phone' => ['required', 'string'],
            'customer_name' => ['nullable', 'string'],
            'type' => ['required', 'in:dine_in,takeaway'],
            'table_id' => ['nullable', 'exists:restaurant_tables,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_item_id' => ['required', 'exists:menu_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'total' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $restaurant = \App\Models\Restaurant::first();

        // Update table status if dine-in
        if ($validated['type'] === 'dine_in' && !empty($validated['table_id'])) {
            $table = \App\Models\Table::find($validated['table_id']);
            if ($table) {
                $table->update(['status' => 'occupied']);
            }
        }

        // Find or create customer
        $customer = $this->loyaltyService->findOrCreateCustomer(
            $restaurant,
            $validated['customer_phone'],
            $validated['customer_name'] ?? null
        );

        // Create order
        $order = Order::create([
            'restaurant_id' => $restaurant->id,
            'customer_id' => $customer->id,
            'order_number' => 'ORD-' . strtoupper(Str::random(8)),
            'status' => 'pending',
            'type' => $validated['type'],
            'table_id' => $validated['table_id'] ?? null,
            'subtotal' => $validated['subtotal'],
            'tax' => $validated['tax'] ?? 0,
            'total' => $validated['total'],
            'currency' => $restaurant->currency ?? 'AED',
            'customer_name' => $validated['customer_name'] ?? $customer->name,
            'customer_phone' => $validated['customer_phone'],
            'notes' => $validated['notes'] ?? null,
        ]);

        // Create order items
        foreach ($validated['items'] as $item) {
            $order->items()->create([
                'menu_item_id' => $item['menu_item_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        return redirect()->back()->with('message', __('orders.order_created'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,processing,completed,cancelled'],
        ]);

        $oldStatus = $order->status;
        $order->update([
            'status' => $validated['status'],
            'completed_at' => $validated['status'] === 'completed' ? now() : null,
        ]);

        // Process loyalty points when order is completed
        if ($validated['status'] === 'completed' && $oldStatus !== 'completed') {
            $this->loyaltyService->processOrderPoints($order);
        }

        return redirect()->back()->with('message', __('orders.status_updated'));
    }
}

