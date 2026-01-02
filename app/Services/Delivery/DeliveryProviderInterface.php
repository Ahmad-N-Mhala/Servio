<?php

namespace App\Services\Delivery;

use App\Models\DeliveryIntegration;
use App\Models\Order;

interface DeliveryProviderInterface
{
    /**
     * Parse incoming webhook payload into a standardized array.
     *
     * @param array $payload
     * @return array Standardized order data ['external_id', 'total', 'customer' => [], 'items' => []]
     */
    public function parseOrderPayload(array $payload): array;

    /**
     * Verify the webhook signature to ensure authenticity.
     *
     * @param \Illuminate\Http\Request $request
     * @param DeliveryIntegration $integration
     * @return bool
     */
    public function verifyWebhookSignature(\Illuminate\Http\Request $request, DeliveryIntegration $integration): bool;

    /**
     * Push the restaurant menu to the delivery platform.
     *
     * @param DeliveryIntegration $integration
     * @param array $menuData Formatted menu data
     * @return bool Success status
     */
    public function pushMenu(DeliveryIntegration $integration, array $menuData): bool;

    /**
     * Confirm order acceptance to the delivery platform.
     *
     * @param DeliveryIntegration $integration
     * @param string $externalOrderId
     * @return bool
     */
    public function acceptOrder(DeliveryIntegration $integration, string $externalOrderId): bool;

    /**
     * Reject order on the delivery platform.
     *
     * @param DeliveryIntegration $integration
     * @param string $externalOrderId
     * @param string $reason
     * @return bool
     */
    public function rejectOrder(DeliveryIntegration $integration, string $externalOrderId, string $reason): bool;
}
