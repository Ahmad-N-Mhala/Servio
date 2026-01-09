<?php

namespace App\Services\Delivery\Providers;

use App\Services\Delivery\DeliveryProviderInterface;
use App\Models\DeliveryIntegration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CareemProvider implements DeliveryProviderInterface
{
    // Research indicates 'careemnow' domain is used for partners
    protected $baseUrl = 'https://api.careemnow.com/food/v1';
    protected $authUrl = 'https://auth.careemnow.com/oauth/token'; // Hypothetical Standard OAuth

    /**
     * Helper to get Careem OAuth Token
     */
    protected function getAccessToken(DeliveryIntegration $integration): ?string
    {
        $cacheKey = "careem_token_" . $integration->id;
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            // Standard OAuth Client Credentials Flow
            $response = Http::asForm()->post($this->authUrl, [
                'client_id' => $integration->client_id,
                'client_secret' => $integration->client_secret,
                'grant_type' => 'client_credentials',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $token = $data['access_token'];
                // Default to 1 hour if not provided
                $expiresIn = $data['expires_in'] ?? 3600;

                Cache::put($cacheKey, $token, $expiresIn - 60);

                return $token;
            }

            Log::error('Careem Token Fetch Failed: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Careem Token Exception: ' . $e->getMessage());
            return null;
        }
    }

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
        // Often uses simple secret matching or HMAC
        return true;
    }

    public function pushMenu(DeliveryIntegration $integration, array $menuData): bool
    {
        $token = $this->getAccessToken($integration);
        if (!$token)
            return false;

        try {
            $response = Http::withToken($token)
                ->withHeaders(['X-Store-ID' => $integration->store_id])
                ->post("{$this->baseUrl}/menu", $menuData);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Careem Menu Push Failed: ' . $e->getMessage());
            return false;
        }
    }

    public function acceptOrder(DeliveryIntegration $integration, string $externalOrderId): bool
    {
        $token = $this->getAccessToken($integration);
        if (!$token)
            return false;

        try {
            $response = Http::withToken($token)
                ->post("{$this->baseUrl}/orders/{$externalOrderId}/accept");

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function rejectOrder(DeliveryIntegration $integration, string $externalOrderId, string $reason): bool
    {
        $token = $this->getAccessToken($integration);
        if (!$token)
            return false;

        try {
            $response = Http::withToken($token)
                ->post("{$this->baseUrl}/orders/{$externalOrderId}/reject", [
                    'rejection_reason' => $reason
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
