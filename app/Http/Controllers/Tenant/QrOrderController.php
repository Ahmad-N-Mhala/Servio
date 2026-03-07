<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\Customer;
use App\Services\LoyaltyService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QrOrderController extends Controller
{
    public function __construct(protected LoyaltyService $loyaltyService)
    {
    }

    /**
     * Show the public menu for QR code ordering
     */
    public function showMenu(string $token): Response
    {
        $table = Table::where('qr_code_token', $token)->firstOrFail();
        $restaurant = $table->restaurant;

        if (!$restaurant->hasFeature('qr_ordering')) {
            abort(403, 'QR Ordering is not enabled for this restaurant.');
        }

        // Get raw ingredient stocks for frontend validation
        $ingredientStocks = \App\Models\Ingredient::where('restaurant_id', $restaurant->id)
            ->get(['id', 'current_stock', 'name'])
            ->mapWithKeys(function ($ing) {
                return [
                    (string) $ing->id => [
                        'current_stock' => (float) $ing->current_stock,
                        'name' => $ing->name
                    ]
                ];
            });

        // Pre-fetch all available batches
        $allBatches = \App\Models\IngredientBatch::where('restaurant_id', $restaurant->id)
            ->where('quantity_remaining', '>', 0)
            ->get()
            ->groupBy('ingredient_id')
            ->map(fn($batches) => $batches->sum('quantity_remaining'));

        $menuItemStockInfo = [];

        $categories = MenuCategory::where('restaurant_id', $restaurant->id)
            ->with([
                'items' => function ($query) {
                    $query->with(['ingredients', 'extras', 'bundles.childItem'])
                        ->orderBy('sort_order');
                }
            ])
            ->orderBy('sort_order')
            ->get()
            ->filter(function ($category) {
                return (bool) $category->is_active;
            })
            ->values()
            ->map(function ($category) use ($allBatches, &$menuItemStockInfo) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'items' => $category->items->filter(fn($item) => (bool) $item->is_available)->map(function ($item) use ($allBatches, &$menuItemStockInfo) {
                        // Calculate stock
                        $maxServings = PHP_INT_MAX;
                        if ($item->ingredients->isNotEmpty()) {
                            foreach ($item->ingredients as $ingredient) {
                                if (!$ingredient->pivot || !isset($ingredient->pivot->quantity))
                                    continue;
                                $required = $ingredient->pivot->quantity;
                                $available = $allBatches[(string) $ingredient->id] ?? 0;
                                if ($required > 0) {
                                    $maxServings = min($maxServings, floor((float) $available / (float) $required));
                                }
                            }
                        }
                        if ($maxServings === PHP_INT_MAX)
                            $maxServings = 999;
                        $menuItemStockInfo[$item->id] = [
                            'max_quantity' => (int) $maxServings,
                            'available' => $maxServings > 0,
                            'is_tracked' => $item->ingredients->isNotEmpty(),
                        ];

                        $item->append('inventory_status');

                        // Fix image URL
                        $imageUrl = null;
                        if ($item->image) {
                            $imageUrl = str_starts_with($item->image, 'http') ? $item->image : asset('storage/' . $item->image);
                        }

                        return [
                            'id' => $item->id,
                            'name' => $item->name,
                            'description' => $item->description,
                            'price' => (float) $item->price,
                            'image' => $imageUrl,
                            'images' => $item->images,
                            'currency' => $item->currency ?? 'AED',
                            'extras' => $item->extras,
                            'inventory_status' => $item->inventory_status,
                            'type' => $item->type ?? 'item',
                            'bundles' => $item->bundles,
                            'recipe' => $item->recipe ?? $item->ingredients->map(function ($i) {
                                return ['ingredient_id' => $i->id, 'quantity' => $i->pivot->quantity];
                            })->all(),
                        ];
                    })->values()->toArray(),
                ];
            })
            ->toArray();

        return Inertia::render('Public/QrMenu', [
            'table' => [
                'id' => $table->id,
                'name' => $table->name,
                'token' => $table->qr_code_token,
            ],
            'restaurant' => [
                'name' => $restaurant->name,
                'currency' => $restaurant->currency ?? 'AED',
                'locale' => $restaurant->locale ?? 'en',
                'google_map_location' => $restaurant->google_map_location,
            ],
            'categories' => $categories,
            'stockAvailability' => $menuItemStockInfo,
            'ingredientStocks' => $ingredientStocks,
        ]);
    }

    /**
     * Place an order from QR code
     */
    public function placeOrder(Request $request, string $token)
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'exists:menu_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
            'items.*.extras' => ['nullable', 'array'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
            'reward_id' => ['nullable', 'string', 'exists:rewards,id'],
            'otp' => ['nullable', 'string', 'size:6'],
        ]);

        $table = Table::where('qr_code_token', $token)->firstOrFail();
        $restaurant = $table->restaurant;

        // Find or create customer
        $customerObj = null;
        if (!empty($validated['customer_phone'])) {
            $customerObj = $this->loyaltyService->findOrCreateCustomer(
                $restaurant,
                $validated['customer_phone'],
                $validated['customer_name'] ?? 'QR Guest'
            );
        }

        // ====== STOCK VALIDATION ======
        $stockErrors = [];
        $menuItemIds = collect($validated['items'])->pluck('id')->unique()->toArray();
        $menuItems = MenuItem::with('ingredients')->whereIn('id', $menuItemIds)->get()->keyBy('id');

        $ingredientIds = [];
        foreach ($menuItems as $item) {
            if ($item->ingredients->isNotEmpty()) {
                $ingredientIds = array_merge($ingredientIds, $item->ingredients->pluck('id')->toArray());
            }
        }
        $ingredientIds = array_unique($ingredientIds);

        $allBatches = \App\Models\IngredientBatch::whereIn('ingredient_id', $ingredientIds)
            ->where('quantity_remaining', '>', 0)
            ->get()
            ->groupBy('ingredient_id')
            ->map(fn($batches) => $batches->sum('quantity_remaining'));

        foreach ($validated['items'] as $item) {
            $menuItem = $menuItems[$item['id']] ?? null;
            if ($menuItem && $menuItem->ingredients->isNotEmpty()) {
                foreach ($menuItem->ingredients as $ingredient) {
                    if (!$ingredient->pivot || !isset($ingredient->pivot->quantity))
                        continue;
                    $neededQty = $ingredient->pivot->quantity * $item['quantity'];
                    $availableStock = $allBatches[(string) $ingredient->id] ?? 0;

                    if ($availableStock < $neededQty) {
                        $menuItemName = is_array($menuItem->name) ? ($menuItem->name['en'] ?? reset($menuItem->name)) : $menuItem->name;
                        $stockErrors[] = "{$menuItemName} - Insufficient stock for '{$ingredient->name}'";
                    }
                }
            }
        }
        if (!empty($stockErrors)) {
            return response()->json(['success' => false, 'message' => implode('. ', $stockErrors)], 422);
        }
        // ====== END STOCK VALIDATION ======

        // Calculate Totals and Prepare Items
        $subtotal = 0;
        $orderItemsData = [];

        foreach ($validated['items'] as $item) {
            $menuItem = $menuItems[$item['id']] ?? null;
            if (!$menuItem)
                continue;

            $extrasCost = 0;
            $itemExtrasNormalized = [];
            if (!empty($item['extras'])) {
                foreach ($item['extras'] as $extra) {
                    $extraModel = \App\Models\MenuItemExtra::find($extra['id']);
                    if ($extraModel && (string) $extraModel->menu_item_id === (string) $menuItem->id) {
                        $extrasCost += (float) $extraModel->price;
                        $itemExtrasNormalized[] = [
                            'id' => $extraModel->id,
                            'name' => $extraModel->name,
                            'price' => (float) $extraModel->price,
                            'ingredient_id' => $extraModel->ingredient_id
                        ];
                    }
                }
            }

            $lineUnitPrice = (float) $menuItem->price;
            $lineTotal = ($lineUnitPrice + $extrasCost) * $item['quantity'];
            $subtotal += $lineTotal;

            $itemName = is_array($menuItem->name) ? ($menuItem->name['en'] ?? reset($menuItem->name)) : $menuItem->name;

            $orderItemsData[] = [
                'menu_item_id' => $menuItem->id,
                'name' => $itemName,
                'quantity' => $item['quantity'],
                'unit_price' => $lineUnitPrice,
                'total_price' => $lineTotal,
                'notes' => $item['notes'] ?? null,
                'extras' => $itemExtrasNormalized
            ];
        }

        // Handle Reward Redemption
        $discountAmount = 0;
        $redemptionRecord = null;
        if (!empty($validated['reward_id']) && $customerObj) {
            $reward = \App\Models\Reward::find($validated['reward_id']);
            if (!$reward) {
                return response()->json(['message' => 'Reward not found.'], 404);
            }

            if ($reward->min_order_value > 0 && $subtotal < $reward->min_order_value) {
                return response()->json(['message' => "Minimum order value of " . (string) $reward->min_order_value . " required for this reward."], 422);
            }

            if (empty($validated['otp'])) {
                return response()->json(['message' => 'OTP is required for redemption.'], 422);
            }

            if (!$this->loyaltyService->verifyOtp($customerObj, $validated['otp'])) {
                return response()->json(['message' => 'Invalid or expired OTP.'], 422);
            }

            $redemptionRecord = $this->loyaltyService->redeemReward($customerObj, (string) $validated['reward_id']);

            if ($reward->reward_type === 'percentage') {
                $discountAmount = $subtotal * ($reward->discount_value / 100);
            } elseif ($reward->reward_type === 'fixed') {
                $discountAmount = (float) $reward->discount_value;
            }
            $discountAmount = min($discountAmount, $subtotal);
        }

        // Generate Order Number with Retries
        $order = null;
        $maxRetries = 5;
        for ($i = 0; $i < $maxRetries; $i++) {
            $nextNumber = $restaurant->next_order_number ?? 1;
            $orderNumber = 'QR-' . $nextNumber;

            try {
                $tax = ($subtotal - $discountAmount) * 0.05; // 5% tax on net
                $total = max(0, $subtotal - $discountAmount + $tax);

                $order = Order::create([
                    'restaurant_id' => $restaurant->id,
                    'table_id' => $table->id,
                    'customer_id' => $customerObj ? $customerObj->id : null,
                    'order_number' => $orderNumber,
                    'transaction_number' => (string) $nextNumber,
                    'type' => 'dine_in',
                    'status' => 'pending',
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'discount_amount' => $discountAmount,
                    'total' => $total,
                    'payment_status' => 'unpaid',
                    'payment_method' => 'cash',
                    'customer_name' => $validated['customer_name'] ?? ($customerObj ? $customerObj->name : 'QR Guest'),
                    'customer_phone' => $validated['customer_phone'] ?? null,
                    'source' => 'qr_code',
                    'ordered_at' => now(),
                    'currency' => $restaurant->currency ?? 'AED',
                ]);

                $restaurant->increment('next_order_number');
                break;
            } catch (\Exception $e) {
                if (str_contains($e->getMessage(), 'E11000 duplicate key error')) {
                    $restaurant->increment('next_order_number');
                    $restaurant->refresh();
                    continue;
                }
                throw $e;
            }
        }

        if (!$order)
            throw new \Exception("Failed to generate order number.");

        if ($redemptionRecord) {
            $redemptionRecord->markAsUsed((string) $order->id);
        }

        // Create Order Items
        foreach ($orderItemsData as $itemData) {
            $order->items()->create($itemData);
        }

        // Update table status
        $table->update(['status' => 'occupied']);

        // Broadcast
        broadcast(new \App\Events\OrderUpdated($order->load(['items.menuItem', 'customer', 'table']), 'created'))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Order placed successfully!',
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'total' => $order->total,
                'table_name' => $table->name,
                'points_earned' => $order->points_earned
            ],
        ]);
    }

    /**
     * Get order status
     */
    public function getOrderStatus(string $token, string $orderNumber)
    {
        $table = Table::where('qr_code_token', $token)->firstOrFail();

        $order = Order::where('order_number', $orderNumber)
            ->where('table_id', $table->id)
            ->with('items')
            ->firstOrFail();

        return response()->json([
            'order' => [
                'order_number' => $order->order_number,
                'status' => $order->status,
                'total' => $order->total,
                'items' => $order->items,
                'created_at' => $order->created_at,
            ],
        ]);
    }

    /**
     * Check Loyalty points and available rewards for a phone number
     */
    public function checkLoyalty(Request $request, string $token)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $table = Table::where('qr_code_token', $token)->firstOrFail();
        $restaurant = $table->restaurant;

        if (!$restaurant->hasFeature('loyalty')) {
            return response()->json(['success' => false, 'message' => 'Loyalty not active']);
        }

        $rawPhone = $validated['phone'];
        // Normalize: strip all non-digit characters for matching
        $digitsOnly = preg_replace('/\D/', '', $rawPhone);

        // withoutGlobalScopes() — QR is a public route with no session/auth,
        // so RestaurantScope would add no filter OR the wrong filter.
        // We handle restaurant scoping explicitly via restaurant_id.
        $customer = Customer::withoutGlobalScopes()
            ->where('restaurant_id', $restaurant->id)
            ->where(function ($q) use ($rawPhone, $digitsOnly) {
                $q->where('phone', $rawPhone)
                    ->orWhere('phone', $digitsOnly)
                    ->orWhere('phone', '+' . $digitsOnly);
            })
            ->with(['loyaltyPoints'])
            ->first();

        if (!$customer) {
            return response()->json(['success' => true, 'found' => false]);
        }

        // Use the model accessor which safely falls back to 0
        $points = (int) ($customer->current_points ?? 0);

        $availableRewards = \App\Models\Reward::withoutGlobalScopes()
            ->where('restaurant_id', $restaurant->id)
            ->where('is_active', true)
            ->where('points_required', '<=', $points)
            ->get();

        return response()->json([
            'success' => true,
            'found' => true,
            'customer' => [
                'name' => $customer->name,
                'points' => $points,
                'tier' => $customer->loyalty_tier
            ],
            'rewards' => $availableRewards
        ]);

    }

    /**
     * Send OTP to customer to redeem a selected reward on QR Menu
     */
    public function requestRedemptionOtp(Request $request, string $token)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'reward_id' => ['required', 'string', 'exists:rewards,id'],
        ]);

        $table = Table::where('qr_code_token', $token)->firstOrFail();

        $customer = Customer::where('restaurant_id', $table->restaurant_id)
            ->where('phone', $validated['phone'])
            ->first();

        if (!$customer) {
            return response()->json(['message' => __('loyalty.customer_not_found')], 404);
        }

        try {
            $sent = $this->loyaltyService->sendRedemptionOtp($customer);
            if ($sent) {
                return response()->json(['success' => true, 'message' => __('loyalty.otp_send_success')]);
            }
            return response()->json(['success' => false, 'message' => __('loyalty.otp_send_failed')], 503);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
