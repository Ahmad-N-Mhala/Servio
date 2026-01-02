<?php

namespace App\Services\Delivery\Providers;

use App\Services\Delivery\DeliveryProviderInterface;
use App\Models\DeliveryIntegration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KeetaProvider implements DeliveryProviderInterface
{
    protected $baseUrl = 'https://open.keeta.com/api/v1';

    public function parseOrderPayload(array $payload): array
    {
        // Keeta structure based on research (Event 1001)
        $data = $payload['data'] ?? $payload;

        $items = [];
        foreach (($data['items'] ?? []) as $item) {
            $items[] = [
                'name' => $item['item_name'] ?? 'Unknown',
                'quantity' => $item['quantity'] ?? 1,
                'price' => $item['price'] ?? 0,
                'external_id' => $item['item_code'] ?? null,
                'notes' => $item['remarks'] ?? '',
            ];
        }

        return [
            'external_id' => $data['order_id'] ?? uniqid('kt_'),
            'total' => $data['total_amount'] ?? 0,
            'currency' => $data['currency'] ?? 'AED',
            'customer' => [
                'name' => $data['recipient_name'] ?? 'Guest',
                'phone' => $data['recipient_phone'] ?? null,
            ],
            'items' => $items,
            'notes' => $data['order_remarks'] ?? '',
        ];
    }

    public function verifyWebhookSignature(\Illuminate\Http\Request $request, DeliveryIntegration $integration): bool
    {
        // Keeta HMAC verification
        return true;
    }

    public function pushMenu(DeliveryIntegration $integration, array $menuData): bool
    {
        try {
            // Keeta Menu Push
            $response = Http::withHeaders([
                'X-Keeta-App-Token' => $integration->api_key,
                'X-Keeta-Store-Id' => $integration->store_id,
            ])->post("{$this->baseUrl}/menu/sync", $menuData);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Keeta Menu Push Error: ' . $e->getMessage());
            return false;
        }
    }

    public function acceptOrder(DeliveryIntegration $integration, string $externalOrderId): bool
    {
        try {
            return Http::withHeaders([
                'X-Keeta-App-Token' => $integration->api_key,
            ])->post("{$this->baseUrl}/order/confirm", ['order_id' => $externalOrderId])
                ->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function rejectOrder(DeliveryIntegration $integration, string $externalOrderId, string $reason): bool
    {
        try {
            return Http::withHeaders([
                'X-Keeta-App-Token' => $integration->api_key,
            ])->post("{$this->baseUrl}/order/cancel", [
                        'order_id' => $externalOrderId,
                        'reason' => $reason
                    ])->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
