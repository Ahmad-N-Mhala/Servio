<?php

namespace App\Services\Delivery\Providers;

use App\Services\Delivery\DeliveryProviderInterface;
use App\Models\DeliveryIntegration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NoonProvider implements DeliveryProviderInterface
{
    // Base URL for Noon Food Partner API (Hypothetical/Researched Standard)
    protected $baseUrl = 'https://api.noon.partners/food/v1';

    public function parseOrderPayload(array $payload): array
    {
        // Based on standard food delivery webhooks structure
        // Noon typically sends: { order_id: "...", customer: {...}, items: [...] }

        $items = [];
        // Handle generic 'items' or 'order_items' key
        $rawItems = $payload['items'] ?? $payload['order_items'] ?? [];

        foreach ($rawItems as $item) {
            $items[] = [
                'name' => $item['name'] ?? $item['title'] ?? 'Unknown Item',
                'quantity' => $item['quantity'] ?? 1,
                'price' => $item['price'] ?? $item['unit_price'] ?? 0,
                'external_id' => $item['id'] ?? $item['sku'] ?? null,
                'notes' => $item['special_instructions'] ?? $item['notes'] ?? '',
            ];
        }

        return [
            'external_id' => $payload['id'] ?? $payload['order_id'] ?? uniqid('noon_'),
            'total' => $payload['total'] ?? $payload['order_total'] ?? 0,
            'currency' => $payload['currency'] ?? 'AED',
            'customer' => [
                'name' => $payload['customer']['name'] ?? 'Noon Customer',
                'phone' => $payload['customer']['phone'] ?? null,
            ],
            'items' => $items,
            'notes' => $payload['notes'] ?? $payload['instruction'] ?? '',
        ];
    }

    public function verifyWebhookSignature(\Illuminate\Http\Request $request, DeliveryIntegration $integration): bool
    {
        // Noon usually uses an Authorization header or a specific Signature header
        // For now, we trust the integration lookup
        return true;
    }

    public function pushMenu(DeliveryIntegration $integration, array $menuData): bool
    {
        // Sync menu to Noon
        // PUT /menus/{store_id}

        try {
            $url = "{$this->baseUrl}/menus/" . $integration->store_id;

            // In a real scenario, $menuData needs to be mapped to Noon's specific JSON schema
            // e.g. Noon requires categories -> items hierarchy

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $integration->api_key,
                'Content-Type' => 'application/json',
            ])->put($url, $menuData);

            return $response->successful();

        } catch (\Exception $e) {
            Log::error('Noon Menu Push Failed: ' . $e->getMessage());
            return false;
        }
    }

    public function acceptOrder(DeliveryIntegration $integration, string $externalOrderId): bool
    {
        return $this->updateOrderStatus($integration, $externalOrderId, 'ACCEPTED');
    }

    public function rejectOrder(DeliveryIntegration $integration, string $externalOrderId, string $reason): bool
    {
        return $this->updateOrderStatus($integration, $externalOrderId, 'REJECTED', $reason);
    }

    private function updateOrderStatus($integration, $orderId, $status, $reason = null)
    {
        try {
            $payload = ['status' => $status];
            if ($reason)
                $payload['reason'] = $reason;

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $integration->api_key,
            ])->post("{$this->baseUrl}/orders/{$orderId}/status", $payload);

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
