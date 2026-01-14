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
            $redemption->update(['status' => 'cancelled']);
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
        $transaction->update(['reward_redemption_id' => $redemption->id]);

        // Update reward redemption count
        $reward->increment('redemptions_count');

        return $redemption;
    }

    public function sendRedemptionOtp(Customer $customer): bool
    {
        if (!$customer->phone) {
            return false;
        }

        // Generate 6-digit OTP
        $otpCode = (string) rand(100000, 999999);

        // Store OTP
        \App\Models\CustomerOtp::create([
            'customer_id' => $customer->id,
            'phone' => $customer->phone,
            'otp' => $otpCode,
            'expires_at' => now()->addMinutes(10),
            'is_used' => false,
            'type' => 'redemption',
        ]);

        // Determine Driver for Logging/Simulation check
        $driver = config('services.sms.driver', 'log');

        try {
            // If driver is 'log', we simulate success (Demo Mode) and explicitly log the OTP
            if ($driver === 'log') {
                \Illuminate\Support\Facades\Log::info("SIMULATED SMS to {$customer->phone}: OTP {$otpCode}");

                \App\Models\CommunicationLog::create([
                    'restaurant_id' => $customer->restaurant_id,
                    'recipient' => $customer->phone,
                    'type' => 'sms',
                    'status' => 'sent',
                    'message' => "OTP for redemption: {$otpCode} (Simulated - Log Driver)",
                    'sent_at' => now(),
                ]);

                return true;
            }

            // Real Send Logic
            $message = "Your Restrufy redemption code is: {$otpCode}. Valid for 10 minutes.";
            $restaurant = \App\Models\Restaurant::find($customer->restaurant_id);

            // Check Balance
            if ($restaurant && $restaurant->sms_balance <= 0) {
                \Illuminate\Support\Facades\Log::warning("Restaurant {$restaurant->id} out of SMS credits.");
                \App\Models\CommunicationLog::create([
                    'restaurant_id' => $customer->restaurant_id,
                    'recipient' => $customer->phone,
                    'type' => 'sms',
                    'status' => 'failed',
                    'message' => "OTP for redemption: {$otpCode}",
                    'error_message' => 'Insufficient SMS Balance',
                    'sent_at' => now(),
                ]);
                return false;
            }

            // Use the centralized SmsService
            app(\App\Services\SmsService::class)->send($customer->phone, $message);

            if ($restaurant) {
                $restaurant->decrement('sms_balance');
            }

            // Log Success
            \App\Models\CommunicationLog::create([
                'restaurant_id' => $customer->restaurant_id,
                'recipient' => $customer->phone,
                'type' => 'sms',
                'status' => 'sent',
                'message' => "OTP for redemption: {$otpCode}",
                'sent_at' => now(),
            ]);

            return true;

        } catch (\Exception $e) {
            // Log Failure
            \App\Models\CommunicationLog::create([
                'restaurant_id' => $customer->restaurant_id,
                'recipient' => $customer->phone,
                'type' => 'sms',
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'message' => "OTP for redemption: {$otpCode}",
                'sent_at' => now(),
            ]);

            \Illuminate\Support\Facades\Log::error("Failed to send OTP SMS: " . $e->getMessage());
            return false;
        }
    }

    public function verifyOtp(Customer $customer, string $otp): bool
    {
        $validOtp = \App\Models\CustomerOtp::where('customer_id', $customer->id)
            ->where('otp', $otp)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if ($validOtp) {
            $validOtp->update(['is_used' => true]);
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

