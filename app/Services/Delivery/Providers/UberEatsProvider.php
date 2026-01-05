<?php

namespace App\Services\Delivery\Providers;

use App\Services\Delivery\DeliveryProviderInterface;
use App\Models\DeliveryIntegration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UberEatsProvider implements DeliveryProviderInterface
{
    protected $baseUrl = 'https://api.uber.com/v2/eats';

    public function parseOrderPayload(array $payload): array
    {
        // Uber Eats generic structure adaptation
        // Based on standard Uber Eats API 'order_created' event

        $orderId = $payload['id'] ?? $payload['order_id'] ?? uniqid('uber_');

        $items = [];
        if (isset($payload['cart']['items'])) {
            foreach ($payload['cart']['items'] as $item) {
                $items[] = [
                    'name' => $item['title'] ?? 'Unknown Item',
                    'quantity' => $item['quantity'] ?? 1,
                    'price' => isset($item['price']['amount']) ? $item['price']['amount'] / 100 : 0, // Uber often in cents
                    'external_id' => $item['external_id'] ?? $item['id'] ?? null,
                    'notes' => $item['special_instructions'] ?? '',
                ];
            }
        }

        $customerName = 'Guest';
        $customerPhone = '';

        if (isset($payload['eater'])) {
            $customerName = ($payload['eater']['first_name'] ?? '') . ' ' . ($payload['eater']['last_name'] ?? '');
            $customerPhone = $payload['eater']['phone'] ?? '';
        }

        $total = isset($payload['payment']['charges']['total']['amount'])
            ? $payload['payment']['charges']['total']['amount'] / 100
            : 0;

        return [
            'external_id' => $orderId,
            'total' => $total,
            'currency' => $payload['payment']['charges']['total']['currency_code'] ?? 'AED',
            'customer' => [
                'name' => trim($customerName) ?: 'Guest',
                'phone' => $customerPhone,
            ],
            'items' => $items,
            'status' => 'pending_approval',
            'notes' => $payload['display_id'] ?? '', // Display ID often used as note or reference
        ];
    }

    public function verifyWebhookSignature(\Illuminate\Http\Request $request, DeliveryIntegration $integration): bool
    {
        // Validation logic for Uber Eats (HMAC-SHA256 usually)
        return true;
    }

    public function pushMenu(DeliveryIntegration $integration, array $menuData): bool
    {
        // Placeholder for Uber Eats Menu API
        try {
            $response = Http::withToken($integration->api_key)
                ->post("{$this->baseUrl}/stores/{$integration->store_id}/menus", [
                    'menus' => [$menuData]
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Uber Eats Menu Push Failed: ' . $e->getMessage());
            return false;
        }
    }

    public function acceptOrder(DeliveryIntegration $integration, string $externalOrderId): bool
    {
        try {
            $response = Http::withToken($integration->api_key)
                ->post("{$this->baseUrl}/orders/{$externalOrderId}/accept", [
                    'reason' => 'PosConfirmed'
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function rejectOrder(DeliveryIntegration $integration, string $externalOrderId, string $reason): bool
    {
        try {
            $response = Http::withToken($integration->api_key)
                ->post("{$this->baseUrl}/orders/{$externalOrderId}/deny", [
                    'reason' => [
                        'explanation' => $reason,
                        'code' => 'KITCHEN_FULL'
                    ]
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
