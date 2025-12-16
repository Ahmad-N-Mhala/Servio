<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Customer;
use App\Models\LoyaltyPoint;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\RewardRedemption;
use Illuminate\Support\Facades\DB;

class LoyaltyService
{
    protected int $pointsPerCurrency = 1; // 1 point per 1 currency unit (e.g., 1 AED = 1 point)
    protected int $pointsExpiryDays = 365; // Points expire after 1 year

    public function findOrCreateCustomer(Restaurant $restaurant, string $phone, ?string $name = null, ?string $email = null, ?string $birthDate = null): Customer
    {
        return Customer::firstOrCreate(
            [
                'restaurant_id' => $restaurant->id,
                'phone' => $phone,
            ],
            [
                'name' => $name,
                'email' => $email,
                'birth_date' => $birthDate,
                'loyalty_tier' => 'bronze',
                'is_active' => true,
            ]
        );
    }

    public function processOrderPoints(Order $order): void
    {
        if (!$order->customer_id || $order->status !== 'completed') {
            return;
        }

        $customer = $order->customer;
        if (!$customer) {
            return;
        }

        // Check if order has redeemed reward - if so, no points earned
        $hasRedemption = \App\Models\RewardRedemption::where('order_id', $order->id)->exists();
        if ($hasRedemption) {
            return;
        }

        // ===== NEW: Check if restaurant has active earning method =====
        $earningMethod = \App\Models\EarningMethod::where('restaurant_id', $order->restaurant_id)
            ->where('is_active', true)
            ->where('type', 'order_total') // Only order_total type awards points automatically
            ->first();

        // If no active earning method, don't award points
        if (!$earningMethod) {
            return;
        }

        // Check minimum spend requirement
        if ($earningMethod->min_spent && $order->total < $earningMethod->min_spent) {
            return;
        }
        // ===== END NEW =====

        DB::transaction(function () use ($order, $customer, $earningMethod) {
            // Use earning method configuration for points calculation
            $pointsPerCurrency = $earningMethod->points ?? $this->pointsPerCurrency;
            $currencyAmount = $earningMethod->currency_amount ?? 1;

            // Calculate points: (order total / currency amount) * points
            $pointsEarned = (int) floor(($order->total / $currencyAmount) * $pointsPerCurrency);

            // Apply max points cap if set
            if ($earningMethod->max_points && $pointsEarned > $earningMethod->max_points) {
                $pointsEarned = $earningMethod->max_points;
            }

            if ($pointsEarned > 0) {
                $loyaltyPoints = $customer->loyaltyPoints()->firstOrCreate([
                    'customer_id' => $customer->id,
                ], [
                    'balance' => 0,
                    'total_earned' => 0,
                    'total_redeemed' => 0,
                ]);

                $expiresAt = now()->addDays($this->pointsExpiryDays);
                $loyaltyPoints->addPoints(
                    $pointsEarned,
                    "Points earned from order #{$order->order_number}",
                    $order->id,
                    $expiresAt
                );

                $order->update(['points_earned' => $pointsEarned]);
            }

            // Update customer stats
            $customer->increment('total_orders');
            $customer->increment('total_spent', $order->total);
            $customer->update(['last_order_at' => now()]);
            $customer->updateTier();
        });
    }

    public function redeemReward(Customer $customer, int $rewardId): RewardRedemption
    {
        $reward = \App\Models\Reward::findOrFail($rewardId);

        if (!$reward->isAvailable()) {
            throw new \Exception('Reward is not available');
        }

        $loyaltyPoints = $customer->loyaltyPoints()->firstOrCreate([
            'customer_id' => $customer->id,
        ], [
            'balance' => 0,
            'total_earned' => 0,
            'total_redeemed' => 0,
        ]);

        if ($loyaltyPoints->balance < $reward->points_required) {
            throw new \Exception('Insufficient points');
        }

        return DB::transaction(function () use ($customer, $reward, $loyaltyPoints) {
            // Deduct points
            $rewardName = $reward->name[app()->getLocale()] ?? $reward->name['en'] ?? 'Unknown Reward';
            $loyaltyPoints->redeemPoints(
                $reward->points_required,
                "Redeemed reward: {$rewardName}",
                null
            );

            // Create redemption
            $redemption = \App\Models\RewardRedemption::create([
                'customer_id' => $customer->id,
                'reward_id' => $reward->id,
                'points_used' => $reward->points_required,
                'status' => 'pending',
                'expires_at' => now()->addDays(30), // Redemption valid for 30 days
            ]);

            // Update reward redemption count
            $reward->increment('redemptions_count');

            return $redemption;
        });
    }

    public function getCustomerByPhone(Restaurant $restaurant, string $phone): ?Customer
    {
        return Customer::where('restaurant_id', $restaurant->id)
            ->where('phone', $phone)
            ->first();
    }
}

