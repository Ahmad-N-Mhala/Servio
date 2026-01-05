<?php

namespace App\Services\Delivery\Providers;

use App\Services\Delivery\DeliveryProviderInterface;
use App\Models\DeliveryIntegration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CareemProvider implements DeliveryProviderInterface
{
    protected $baseUrl = 'https://api.careem.com/food/v1';

    public function parseOrderPayload(array $payload): array
    {
        // Careem generic structure placeholder
        // Assuming structure somewhat similar to standard JSON payloads

        $orderId = $payload['order_id'] ?? $payload['id'] ?? uniqid('careem_');

        $items = [];
        if (isset($payload['items'])) {
            foreach ($payload['items'] as $item) {
                $items[] = [
                    'name' => $item['name'] ?? 'Unknown Item',
                    'quantity' => $item['quantity'] ?? 1,
                    'price' => $item['unit_price'] ?? 0,
                    'external_id' => $item['id'] ?? null,
                    'notes' => $item['comment'] ?? '',
                ];
            }
        }

        return [
            'external_id' => $orderId,
            'total' => $payload['total_amount'] ?? 0,
            'currency' => $payload['currency'] ?? 'AED',
            'customer' => [
                'name' => $payload['customer']['name'] ?? 'Guest',
                'phone' => $payload['customer']['mobile_number'] ?? '',
            ],
            'items' => $items,
            'status' => 'pending_approval',
            'notes' => $payload['instruction'] ?? '',
        ];
    }

    public function verifyWebhookSignature(\Illuminate\Http\Request $request, DeliveryIntegration $integration): bool
    {
        // Careem signature logic
        return true;
    }

    public function pushMenu(DeliveryIntegration $integration, array $menuData): bool
    {
        try {
            // Need API Key + Store ID headers usually
            $response = Http::withHeaders([
                'X-API-Key' => $integration->api_key,
                'X-Store-ID' => $integration->store_id
            ])->post("{$this->baseUrl}/menu", $menuData);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Careem Menu Push Failed: ' . $e->getMessage());
            return false;
        }
    }

    public function acceptOrder(DeliveryIntegration $integration, string $externalOrderId): bool
    {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => $integration->api_key
            ])->post("{$this->baseUrl}/orders/{$externalOrderId}/accept");

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function rejectOrder(DeliveryIntegration $integration, string $externalOrderId, string $reason): bool
    {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => $integration->api_key
            ])->post("{$this->baseUrl}/orders/{$externalOrderId}/reject", [
                        'rejection_reason' => $reason
                    ]);

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
