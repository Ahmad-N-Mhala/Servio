<?php

namespace App\Services\Delivery\Providers;

use App\Services\Delivery\DeliveryProviderInterface;
use App\Models\DeliveryIntegration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class UberEatsProvider implements DeliveryProviderInterface
{
    // Verified Base URL
    protected $baseUrl = 'https://api.uber.com/v2/eats';

    // Auth URL
    protected $authUrl = 'https://auth.uber.com/oauth/v2/token';

    /**
     * Helper to get or refresh OAuth Access Token
     */
    protected function getAccessToken(DeliveryIntegration $integration): ?string
    {
        // Use cached token if available
        $cacheKey = "uber_token_" . $integration->id;
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // Request new token
        try {
            $response = Http::asForm()->post($this->authUrl, [
                'client_id' => $integration->client_id,
                'client_secret' => $integration->client_secret,
                'grant_type' => 'client_credentials',
                'scope' => 'eats.store eats.order' // Verified scopes
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $token = $data['access_token'];
                $expiresIn = $data['expires_in'] ?? 3600;

                // Cache it for slightly less than expiry time
                Cache::put($cacheKey, $token, $expiresIn - 60);

                return $token;
            }

            Log::error('Uber Token Fetch Failed: ' . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error('Uber Token Fetch Exception: ' . $e->getMessage());
            return null;
        }
    }

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
        // Validation logic for Uber Eats (HMAC-SHA256)
        // Header: X-Uber-Signature
        $signature = $request->header('X-Uber-Signature');
        if (!$signature)
            return false;

        $computed = hash_hmac('sha256', $request->getContent(), $integration->client_secret);
        return hash_equals($signature, $computed);
    }

    public function pushMenu(DeliveryIntegration $integration, array $menuData): bool
    {
        $token = $this->getAccessToken($integration);
        if (!$token)
            return false;

        try {
            $response = Http::withToken($token)
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
        $token = $this->getAccessToken($integration);
        if (!$token)
            return false;

        try {
            $response = Http::withToken($token)
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
        $token = $this->getAccessToken($integration);
        if (!$token)
            return false;

        try {
            $response = Http::withToken($token)
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
