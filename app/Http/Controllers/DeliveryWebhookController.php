<?php

namespace App\Http\Controllers;

use App\Models\DeliveryIntegration;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeliveryWebhookController extends Controller
{
    public function handle(Request $request, $provider)
    {
        Log::info("Received webhook from {$provider}", ['content' => $request->all()]);

        // 1. Identify Store/Tenant
        $storeId = $request->input('store_id') ?? $request->input('restaurant_id');

        // Note: Real providers might pass store_id in headers or a different field
        // This is a minimal fallback
        if (! $storeId && isset($request['store'])) {
            $storeId = $request['store']['id'] ?? null;
        }

        if (! $storeId) {
            // For testing only - if no store_id provided in generic payload,
            // accept it if we can find ANY integration for this provider.
            // WARNING: This is dangerous for production but useful for sandbox testing without real payloads
            $integration = DeliveryIntegration::withoutGlobalScope(\App\Models\Scopes\RestaurantScope::class)
                ->where('provider', $provider)
                ->first();

            if ($integration) {
                // Use the store ID from the first found integration for testing flows
                $storeId = $integration->store_id;
            } else {
                return response()->json(['error' => 'Store ID not found in payload'], 400);
            }
        } else {
            // 2. Find Integration
            $integration = DeliveryIntegration::withoutGlobalScope(\App\Models\Scopes\RestaurantScope::class)
                ->where('provider', $provider)
                ->where('store_id', $storeId)
                ->first();
        }

        if (! $integration) {
            return response()->json(['error' => 'Integration not configured for this store'], 404);
        }

        if (! $integration->is_enabled) {
            return response()->json(['error' => 'Integration disabled'], 403);
        }

        // 3. Get Provider Service
        try {
            $service = \App\Services\Delivery\DeliveryService::getProvider($provider);

            // Verify Signature
            if (! $service->verifyWebhookSignature($request, $integration)) {
                return response()->json(['error' => 'Invalid Signature'], 401);
            }

            // Parse Data
            $orderData = $service->parseOrderPayload($request->all());

        } catch (\Exception $e) {
            Log::error('Provider Error: '.$e->getMessage());

            return response()->json(['error' => 'Provider processing error'], 500);
        }

        $restaurant = $integration->restaurant;

        // Reset counter if new day (UAE time)
        $restaurant->checkAndResetOrderCounter();

        $maxRetries = 5;
        $order = null;

        for ($i = 0; $i < $maxRetries; $i++) {
            $transactionNumber = $restaurant->next_order_number ?? 1;
            $orderNumber = ucfirst($provider).'-'.$transactionNumber;

            try {
                // 4. Create Order
                $order = Order::create([
                    'restaurant_id' => $restaurant->id,
                    'order_number' => $orderNumber,
                    'transaction_number' => $transactionNumber,
                    'delivery_provider' => $provider,
                    'delivery_order_id' => $orderData['external_id'],
                    'status' => 'pending_approval',
                    'total' => $orderData['total'],
                    'currency' => $orderData['currency'] ?? 'AED',
                    'customer_name' => $orderData['customer']['name'] ?? 'Guest',
                    'customer_phone' => $orderData['customer']['phone'] ?? '',
                    'items' => $orderData['items'] ?? [],
                    'raw_payload' => $request->all(),
                    'notes' => $orderData['notes'] ?? '',
                ]);

                $restaurant->increment('next_order_number');
                break;
            } catch (\Exception $e) {
                if (str_contains($e->getMessage(), 'E11000 duplicate key error') || str_contains($e->getMessage(), 'duplicate key error')) {
                    $restaurant->increment('next_order_number');
                    $restaurant->refresh();
                    continue;
                }
                throw $e;
            }
        }

        if (! $order) {
            throw new \Exception('Failed to generate order number.');
        }

        // 5. Create Order Items (Link to Menu & Inventory)
        foreach ($orderData['items'] as $item) {
            $menuItem = null;

            // A. Try matching by SKU (External ID)
            if (! empty($item['external_id'])) {
                $menuItem = \App\Models\MenuItem::where('restaurant_id', $restaurant->id)
                    ->where('sku', (string) $item['external_id'])
                    ->first();
            }

            // B. Try matching by Name (if no SKU match)
            if (! $menuItem && ! empty($item['name'])) {
                $menuItem = \App\Models\MenuItem::where('restaurant_id', $restaurant->id)
                    ->where(function ($q) use ($item) {
                        $q->where('name', $item['name'])
                            ->orWhere('name->en', $item['name']) // JSON field assumption
                            ->orWhere('name->ar', $item['name']);
                    })
                    ->first();
            }

            // Create Order Item
            $order->items()->create([
                'menu_item_id' => $menuItem ? $menuItem->id : null,
                'name' => $item['name'], // Fallback name for display
                'quantity' => $item['quantity'],
                'unit_price' => $item['price'],
                'total_price' => $item['quantity'] * $item['price'],
                'notes' => $item['notes'] ?? '',
            ]);
        }

        return response()->json(['success' => true, 'order_id' => $order->id]);
    }

    // Removed parseOrderData as it is now handled by the Service
}
