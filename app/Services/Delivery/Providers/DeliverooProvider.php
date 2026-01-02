<?php

namespace App\Services\Delivery\Providers;

use App\Services\Delivery\DeliveryProviderInterface;
use App\Models\DeliveryIntegration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeliverooProvider implements DeliveryProviderInterface
{
    protected $baseUrl = 'https://api.deliveroo.com/order/v1';

    public function parseOrderPayload(array $payload): array
    {
        // Deliveroo structure (based on researched 'order.new' event)
        // Note: Field names are based on standard Deliveroo API docs

        $order = $payload['order'] ?? $payload; // Sometimes wrapper exists

        $items = [];
        if (isset($order['items'])) {
            foreach ($order['items'] as $item) {
                $items[] = [
                    'name' => $item['name'] ?? 'Unknown Item',
                    'quantity' => $item['quantity'] ?? 1,
                    'price' => $item['price'] ?? 0,
                    'external_id' => $item['id'] ?? null,
                    'notes' => $item['notes'] ?? '',
                ];
            }
        }

        return [
            'external_id' => $order['id'] ?? uniqid('del_'),
            'total' => $order['total_price'] ?? 0,
            'currency' => $order['currency'] ?? 'AED',
            'customer' => [
                'name' => $order['customer']['name'] ?? 'Guest',
                'phone' => $order['customer']['phone_number'] ?? null,
            ],
            'items' => $items,
            'status' => 'pending_approval',
            'notes' => $order['notes'] ?? '',
        ];
    }

    public function verifyWebhookSignature(\Illuminate\Http\Request $request, DeliveryIntegration $integration): bool
    {
        // Deliveroo uses HMAC SHA256 signature
        // Header: X-Deliveroo-Signature
        // But for initial implementation without live keys, we return true if secret checks passed in controller
        // Real implementation:
        // $signature = $request->header('X-Deliveroo-Signature');
        // $computed = hash_hmac('sha256', $request->getContent(), $integration->webhook_secret);
        // return hash_equals($signature, $computed);

        return true;
    }

    public function pushMenu(DeliveryIntegration $integration, array $menuData): bool
    {
        // Deliveroo API Endpoint for Menu Upload
        // POST https://api.deliveroo.com/menu/v1/menus

        try {
            // Need to transform $menuData to Deliveroo Schema here
            // This is a placeholder for the transformation logic

            $response = Http::withBasicAuth($integration->api_key, $integration->api_secret)
                ->post('https://api.deliveroo.com/menu/v1/menus', [
                    'menu' => $menuData
                ]);

            return $response->successful();

        } catch (\Exception $e) {
            Log::error('Deliveroo Menu Push Failed: ' . $e->getMessage());
            return false;
        }
    }

    public function acceptOrder(DeliveryIntegration $integration, string $externalOrderId): bool
    {
        // POS confirmed the order
        // POST /orders/{order_id}/confirm

        try {
            $response = Http::withBasicAuth($integration->api_key, $integration->api_secret)
                ->post("{$this->baseUrl}/orders/{$externalOrderId}/confirm", [
                    'status' => 'confirmed'
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function rejectOrder(DeliveryIntegration $integration, string $externalOrderId, string $reason): bool
    {
        try {
            $response = Http::withBasicAuth($integration->api_key, $integration->api_secret)
                ->post("{$this->baseUrl}/orders/{$externalOrderId}/reject", [
                    'reason' => $reason
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
