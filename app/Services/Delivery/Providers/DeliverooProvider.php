<?php

namespace App\Services\Delivery\Providers;

use App\Models\DeliveryIntegration;
use App\Services\Delivery\DeliveryProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeliverooProvider implements DeliveryProviderInterface
{
    // Updated to Production URL based on research
    protected $baseUrl = 'https://api.developers.deliveroo.com/order/v1';

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
        // Deliveroo HMAC SHA256 Signature Verification
        // Headers: X-Deliveroo-Sequence-Guid, X-Deliveroo-Hmac-Sha256

        $signature = $request->header('X-Deliveroo-Hmac-Sha256');
        if (! $signature) {
            return false;
        }

        // According to docs, we might need to concat GUID + Body
        // But simplified HMAC verification usually suffices on the body
        // Docs say: signature = HMAC(SHA256, key=secret, msg=X-Deliveroo-Sequence-Guid + " " + raw_request_body)

        $guid = $request->header('X-Deliveroo-Sequence-Guid');
        $rawPayload = $request->getContent();

        // Construct the expected message string (Guid + Space + Body)
        // Note: For legacy Pos integration it might be different, but for new APIs it's space separated
        $message = $guid ? ($guid.' '.$rawPayload) : $rawPayload;

        $computed = hash_hmac('sha256', $message, $integration->webhook_secret);

        return hash_equals($signature, $computed);
    }

    public function pushMenu(DeliveryIntegration $integration, array $menuData): bool
    {
        // Deliveroo API Endpoint for Menu Upload
        // Production: https://api.developers.deliveroo.com/menu/v1/menus

        try {
            // Need to transform $menuData to Deliveroo Schema here
            // This is a placeholder for the transformation logic

            $response = Http::withBasicAuth($integration->api_key, $integration->api_secret)
                ->post('https://api.developers.deliveroo.com/menu/v1/menus', [
                    'menu' => $menuData,
                ]);

            return $response->successful();

        } catch (\Exception $e) {
            Log::error('Deliveroo Menu Push Failed: '.$e->getMessage());

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
                    'status' => 'confirmed',
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
                    'reason' => $reason,
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
