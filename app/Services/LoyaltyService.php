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
        $customer = Customer::firstOrCreate(
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

        if ($customer->wasRecentlyCreated === false) {
            $updates = [];
            if ($name && $customer->name !== $name)
                $updates['name'] = $name;
            if ($email && $customer->email !== $email)
                $updates['email'] = $email;
            if ($birthDate && $customer->birth_date !== $birthDate)
                $updates['birth_date'] = $birthDate;

            if (!empty($updates)) {
                $customer->update($updates);
            }
        }

        return $customer;
    }

    public function processOrderPoints(Order $order): void
    {
        // Don't process if already earned points (except if 0 specifically handled)
        if ($order->points_earned !== null && $order->points_earned > 0) {
            return;
        }

        if (!$order->customer_id || $order->payment_status !== 'paid') {
            return;
        }

        $customer = $order->customer;
        if (!$customer) {
            return;
        }

        // Check if order has redeemed reward
        $redemption = \App\Models\RewardRedemption::where('order_id', $order->id)->with('reward')->first();
        if ($redemption && $redemption->reward->reward_type !== 'cashback') {
            // Standard rewards block points earning (choice of either points or discount)
            return;
        }

        // ===== NEW: Check if restaurant has active earning method =====
        $earningMethod = \App\Models\EarningMethod::where('restaurant_id', (string) $order->restaurant_id)
            ->where('is_active', true)
            ->first();

        // If no active earning method, don't award points
        if (!$earningMethod) {
            return;
        }

        $pointsEarned = 0;

        if ($earningMethod->type === 'order_total') {
            // Check minimum spend requirement
            if ($earningMethod->min_spent && $order->total < $earningMethod->min_spent) {
                return;
            }

            $pointsPerCurrency = $earningMethod->points ?? $this->pointsPerCurrency;
            $currencyAmount = (float) ($earningMethod->currency_amount ?? 1);

            if ($currencyAmount <= 0) {
                $currencyAmount = 1.0;
            }

            // Calculate points: (order total / currency amount) * points
            $pointsEarned = (int) floor(($order->total / $currencyAmount) * $pointsPerCurrency);

            // Apply max points cap if set
            if ($earningMethod->max_points && $pointsEarned > $earningMethod->max_points) {
                $pointsEarned = $earningMethod->max_points;
            }
        } elseif ($earningMethod->type === 'visit') {
            // Award fixed points per visit (completed order)
            $pointsEarned = (int) ($earningMethod->points ?? 1);
        }

        // Add Cashback Points if applicable
        if ($redemption && $redemption->reward->reward_type === 'cashback') {
            $cashbackPercent = (float) ($redemption->reward->discount_value ?? 0);
            $cashbackPoints = (int) floor(((float) $order->total * ($cashbackPercent / 100)));
            $pointsEarned += $cashbackPoints;
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

        }

        // Always set points_earned to signify it has been processed, even if 0
        $order->update(['points_earned' => $pointsEarned]);

        // Update customer stats
        $customer->increment('total_orders');
        // Cast Decimal128 to float for MongoDB compatibility
        $customer->increment('total_spent', (float) (string) $order->total);
        $customer->update(['last_order_at' => now()]);

        $tierChanged = $customer->updateTier();

        // Send Loyalty Notifications
        $this->sendLoyaltyNotifications($order, $customer, $pointsEarned, $tierChanged);
    }

    private function sendLoyaltyNotifications($order, $customer, $pointsEarned, $tierChanged)
    {
        $variables = [
            'customer_name' => $customer->name,
            'points_earned' => $pointsEarned,
            'points_balance' => $customer->loyaltyPoints->balance ?? 0,
            'loyalty_tier' => $customer->loyalty_tier,
            'order_number' => $order->order_number,
            'restaurant_name' => $order->restaurant->name ?? 'our restaurant',
        ];

        // 1. Points Earned notification
        if ($pointsEarned > 0) {
            $pointTemplate = \App\Models\CommunicationTemplate::where('restaurant_id', (string) $order->restaurant_id)
                ->where('trigger_event', 'loyalty_points_earned')
                ->where('is_active', true)
                ->first();

            if ($pointTemplate) {
                \App\Jobs\SendCustomerCommunicationJob::dispatch($pointTemplate, $customer, $variables);
            }
        }

        // 2. Tier Upgraded notification
        if ($tierChanged) {
            $tierTemplate = \App\Models\CommunicationTemplate::where('restaurant_id', (string) $order->restaurant_id)
                ->where('trigger_event', 'loyalty_tier_upgraded')
                ->where('is_active', true)
                ->first();

            if ($tierTemplate) {
                \App\Jobs\SendCustomerCommunicationJob::dispatch($tierTemplate, $customer, $variables);
            }
        }
    }

    public function recalculateCustomerStats(Customer $customer): void
    {
        $stats = $customer->orders()
            ->where('payment_status', 'paid')
            ->whereNotIn('status', ['cancelled', 'deleted'])
            ->get()
            ->reduce(function ($carry, $order) {
                $carry['total_orders']++;
                $carry['total_spent'] += $order->total;
                return $carry;
            }, ['total_orders' => 0, 'total_spent' => 0.0]);

        $customer->update([
            'total_orders' => $stats['total_orders'],
            'total_spent' => (float) $stats['total_spent'],
        ]);

        $customer->updateTier();
    }

    public function revertOrderPoints(Order $order): void
    {
        if (!$order->customer_id)
            return;

        $customer = $order->customer;
        if (!$customer)
            return;

        // Revert Loyalty Points if any
        if ($order->points_earned > 0) {
            $lp = $customer->loyaltyPoints;
            if ($lp) {
                // Manually revert to correct Earned totals
                $lp->decrement('balance', (int) $order->points_earned);
                $lp->decrement('total_earned', (int) $order->points_earned);
                // Remove transaction log
                $customer->pointTransactions()->where('order_id', $order->id)->delete();
            }

            // Clear points earned on order to prevent double-reversion
            $order->update(['points_earned' => 0]);
        }

        // Revert Redemptions if any (give points back on cancel/delete)
        $redemptions = \App\Models\RewardRedemption::where('order_id', $order->id)->get();
        foreach ($redemptions as $redemption) {
            /** @var \App\Models\RewardRedemption $redemption */
            $lp = $customer->loyaltyPoints;
            if ($lp && $redemption->status === 'applied') {
                $lp->increment('balance', (int) $redemption->points_used);
                $lp->decrement('total_redeemed', (int) $redemption->points_used);

                // Add a record of refund
                \App\Models\PointTransaction::create([
                    'customer_id' => $customer->id,
                    'reward_redemption_id' => $redemption->id,
                    'order_id' => $order->id,
                    'type' => 'earned', // Refunded points count as income to balance
                    'points' => (int) $redemption->points_used,
                    'description' => "Points refunded from cancelled order #{$order->order_number}",
                    'balance_after' => $lp->balance,
                ]);
            }
            $redemption->status = 'cancelled';
            $redemption->save();
        }

        // Recalculate stats to be 100% accurate
        $this->recalculateCustomerStats($customer);
    }

    public function redeemReward(Customer $customer, string $rewardId): RewardRedemption
    {
        $reward = \App\Models\Reward::findOrFail($rewardId);

        // Security check: Ensure reward belongs to the same restaurant as the customer
        if ((string) $reward->restaurant_id !== (string) $customer->restaurant_id) {
            throw new \Exception('Invalid reward for this customer');
        }

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

        // Note: MongoDB transactions require replica sets, executing without transaction
        // Deduct points
        $rewardName = $reward->name[app()->getLocale()] ?? $reward->name['en'] ?? 'Unknown Reward';
        $transaction = $loyaltyPoints->redeemPoints(
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

        // Link transaction to redemption
        /** @var \App\Models\PointTransaction $transaction */
        $transaction->reward_redemption_id = $redemption->id;
        $transaction->save();

        // Update reward redemption count
        $reward->increment('redemptions_count');

        return $redemption;
    }

    public function sendRedemptionOtp(Customer $customer): bool
    {
        $customerId = (string) $customer->id;
        $restaurantId = (string) $customer->restaurant_id;

        \Illuminate\Support\Facades\Log::info("LoyaltyService: sendRedemptionOtp called for Customer: " . $customerId);

        try {
            if (!$customer->phone) {
                \Illuminate\Support\Facades\Log::warning("LoyaltyService: Customer " . $customerId . " has no phone number.");

                \App\Services\CommunicationService::log([
                    'restaurant_id' => $restaurantId,
                    'recipient' => 'N/A',
                    'type' => 'sms',
                    'status' => 'failed',
                    'message' => __('loyalty.otp_send_failed'),
                    'error_message' => 'Missing customer phone'
                ]);

                throw new \Exception(__('loyalty.customer_no_phone'));
            }

            // Generate 6-digit OTP
            $otpCode = (string) rand(100000, 999999);
            \Illuminate\Support\Facades\Log::info("LoyaltyService: Generated OTP for Phone: " . $customer->phone);

            // Store OTP
            \App\Models\CustomerOtp::create([
                'customer_id' => $customer->id,
                'phone' => $customer->phone,
                'otp' => $otpCode,
                'expires_at' => now()->addMinutes(10),
                'is_used' => false,
                'type' => 'redemption',
            ]);

            // Attempt to use Dynamic System/Restaurant Communication Template
            $template = \App\Models\CommunicationTemplate::withoutGlobalScopes()
                ->where('trigger_event', 'loyalty_otp')
                ->where(function ($query) use ($restaurantId) {
                    $query->whereNull('restaurant_id')
                        ->orWhere('restaurant_id', $restaurantId);
                })
                ->where('is_active', true)
                ->orderBy('restaurant_id', 'desc')
                ->first();

            $smsSent = false;

            if ($template && is_array($template->channels) && in_array('sms', $template->channels)) {
                \Illuminate\Support\Facades\Log::info("LoyaltyService: Sending via template ID: " . $template->id);
                $commResults = \App\Services\CustomerCommunicationService::send($template, $customer, ['otp' => $otpCode]);
                $smsResult = $commResults['sms'] ?? null;
                if ($smsResult && !$smsResult['success']) {
                    throw new \Exception($smsResult['error'] ?? __('loyalty.otp_send_failed'));
                }
                $smsSent = true;
            } else {
                \Illuminate\Support\Facades\Log::warning("LoyaltyService: No SMS template found. Falling back to localized system message.");

                $restaurant = \App\Models\Restaurant::find($restaurantId);
                $restaurantName = $restaurant->name ?? config('app.name');

                // Use translated fallback message
                $message = __('loyalty.emergency_otp_message', [
                    'restaurant' => $restaurantName,
                    'otp' => $otpCode
                ]);

                $result = app(\App\Services\SmsService::class)->send($customer->phone, $message);
                $smsSent = $result['success'];

                // Manual Log for Fallback (ensures it shows in SMS logs)
                \App\Services\CommunicationService::log([
                    'restaurant_id' => $restaurantId,
                    'recipient' => $customer->phone,
                    'type' => 'sms',
                    'status' => $result['status'],
                    'message' => $message,
                    'error_message' => $result['error'] ?? ($template ? 'Template found but SMS channel disabled' : 'Template not found - System fallback used'),
                ]);

                if (!$smsSent) {
                    throw new \Exception($result['error'] ?? __('loyalty.otp_send_failed'));
                }
            }

            return $smsSent;

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("LoyaltyService Failure: " . $e->getMessage());
            throw $e; // Re-throw to be handled by controller
        } catch (\Throwable $t) {
            \Illuminate\Support\Facades\Log::error("LoyaltyService Critical: " . $t->getMessage());
            throw new \Exception(__('loyalty.otp_send_failed'));
        }
    }

    public function verifyOtp(Customer $customer, string $otp, bool $markAsUsed = true): bool
    {
        $validOtp = \App\Models\CustomerOtp::where('customer_id', $customer->id)
            ->where('otp', $otp)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if ($validOtp) {
            if ($markAsUsed) {
                $validOtp->is_used = true;
                $validOtp->save();
            }
            return true;
        }

        return false;
    }

    public function getCustomerByPhone(Restaurant $restaurant, string $phone): ?Customer
    {
        return Customer::where('restaurant_id', $restaurant->id)
            ->where('phone', $phone)
            ->first();
    }
}
