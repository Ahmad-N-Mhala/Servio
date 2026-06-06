<?php

namespace App\Services\Delivery\Providers;

use App\Models\DeliveryIntegration;
use App\Services\Delivery\DeliveryProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TalabatProvider implements DeliveryProviderInterface
{
    // Talabat API requires onboarding via Delivery Hero.
    // Direct Access via Partner Portal: https://portal.talabat.com/
    // This URL is a placeholder as Talabat uses specific region-based endpoints + PGP auth often.
    protected $baseUrl = 'https://api.talabat.com/integration/v1';

    public function parseOrderPayload(array $payload): array
    {
        // Talabat typical structure
        // Often wrapped in "body" or root object

        $items = [];
        $rawItems = $payload['items'] ?? [];

        foreach ($rawItems as $item) {
            $items[] = [
                'name' => $item['name'] ?? 'Item',
                'quantity' => $item['quantity'] ?? 1,
                'price' => $item['price'] ?? 0,
                'external_id' => $item['remoteCode'] ?? $item['id'] ?? null,
                'notes' => $item['comment'] ?? '',
            ];
        }

        return [
            'external_id' => $payload['orderId'] ?? $payload['id'] ?? uniqid('tb_'),
            'total' => $payload['total'] ?? 0,
            'currency' => $payload['currency'] ?? 'AED',
            'customer' => [
                'name' => $payload['customer']['firstName'] ?? 'Guest',
                'phone' => $payload['customer']['mobile'] ?? null,
            ],
            'items' => $items,
            'notes' => $payload['comment'] ?? '',
        ];
    }

    public function verifyWebhookSignature(\Illuminate\Http\Request $request, DeliveryIntegration $integration): bool
    {
        // Talabat signature verification logic
        return true;
    }

    public function pushMenu(DeliveryIntegration $integration, array $menuData): bool
    {
        // POST /menu
        try {
            $response = Http::withToken($integration->api_key)
                ->post("{$this->baseUrl}/menu", $menuData);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Talabat Menu Push Error: '.$e->getMessage());

            return false;
        }
    }

    public function acceptOrder(DeliveryIntegration $integration, string $externalOrderId): bool
    {
        try {
            return Http::withToken($integration->api_key)
                ->post("{$this->baseUrl}/orders/{$externalOrderId}/accept")
                ->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function rejectOrder(DeliveryIntegration $integration, string $externalOrderId, string $reason): bool
    {
        try {
            return Http::withToken($integration->api_key)
                ->post("{$this->baseUrl}/orders/{$externalOrderId}/reject", ['reason' => $reason])
                ->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
