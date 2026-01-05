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

        // Note: MongoDB transactions require replica sets, executing without transaction
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
        // Cast Decimal128 to float for MongoDB compatibility
        $customer->increment('total_spent', (float) (string) $order->total);
        $customer->update(['last_order_at' => now()]);
        $customer->updateTier();
    }

    public function redeemReward(Customer $customer, string $rewardId): RewardRedemption
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

        // Note: MongoDB transactions require replica sets, executing without transaction
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

        // Determine Driver
        $driver = config('services.sms.driver', 'log');

        try {
            // If driver is 'log', we simulate success (Demo Mode)
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

            $this->sendSms($driver, $customer->phone, $message);

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

    protected function sendSms(string $driver, string $to, string $message): void
    {
        switch ($driver) {
            case 'twilio':
                $this->sendViaTwilio($to, $message);
                break;
            case 'unifonic':
                $this->sendViaUnifonic($to, $message);
                break;
            case 'sms_ae':
                $this->sendViaSmsAe($to, $message);
                break;
            default:
                // Fallback to log
                \Illuminate\Support\Facades\Log::info("SMS (Log Driver) to {$to}: {$message}");
                break;
        }
    }

    private function sendViaTwilio($to, $message)
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.from');

        if (!$sid || !$token || !$from) {
            throw new \Exception("Twilio credentials missing");
        }

        $response = \Illuminate\Support\Facades\Http::asForm()
            ->withBasicAuth($sid, $token)
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'To' => $to,
                'From' => $from,
                'Body' => $message,
            ]);

        if (!$response->successful()) {
            throw new \Exception("Twilio Error: " . $response->body());
        }
    }

    private function sendViaUnifonic($to, $message)
    {
        $apiKey = config('services.unifonic.api_key');
        $senderId = config('services.unifonic.sender_id');

        if (!$apiKey) {
            throw new \Exception("Unifonic API Key missing");
        }

        $response = \Illuminate\Support\Facades\Http::post("https://el.cloud.unifonic.com/rest/SMS/Messages", [
            'AppSid' => $apiKey,
            'SenderID' => $senderId,
            'Recipient' => $to,
            'Body' => $message,
        ]);

        if (!$response->successful()) {
            throw new \Exception("Unifonic Error: " . $response->body());
        }
    }

    private function sendViaSmsAe($to, $message)
    {
        $user = config('services.sms_ae.username');
        $pass = config('services.sms_ae.password');
        $sender = config('services.sms_ae.sender_id');

        if (!$user || !$pass) {
            throw new \Exception("SMS.ae credentials missing");
        }

        $response = \Illuminate\Support\Facades\Http::get("https://www.sms.ae/api/http/send.aspx", [
            'username' => $user,
            'password' => $pass,
            'recipient' => $to,
            'sender' => $sender,
            'message' => $message,
        ]);

        if (!$response->successful()) {
            throw new \Exception("SMS.ae Error: " . $response->body());
        }
    }

    public function getCustomerByPhone(Restaurant $restaurant, string $phone): ?Customer
    {
        return Customer::where('restaurant_id', $restaurant->id)
            ->where('phone', $phone)
            ->first();
    }
}

