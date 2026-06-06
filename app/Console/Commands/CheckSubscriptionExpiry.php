<?php

namespace App\Console\Commands;

use App\Models\CommunicationTemplate;
use App\Models\Subscription;
use App\Services\CommunicationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckSubscriptionExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:check-expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expiring subscriptions and send notifications';

    /**
     * Execute the console command.
     */
    public function handle(CommunicationService $commService)
    {
        $this->info('Checking subscription expiries...');

        // 1. Check for Warnings (e.g. 3 days before)
        // We find the template first to know how many days before
        $warningTemplate = CommunicationTemplate::where('trigger_event', 'subscription_warning')->where('is_active', true)->first();

        if ($warningTemplate) {
            $days = $warningTemplate->timing_days ?? 3;

            // MongoDB often validates dates strictly, be careful with Timezones.
            // We search for range to be safe.
            $startRange = Carbon::now()->addDays($days)->startOfDay();
            $endRange = Carbon::now()->addDays($days)->endOfDay();

            $expiringSubs = Subscription::where('status', 'active')
                ->whereBetween('ends_at', [$startRange, $endRange])
                ->get();

            foreach ($expiringSubs as $sub) {
                // Get Owner
                // Note: user relationship might be `users` or via pivot
                $owner = null;
                if ($sub->restaurant) {
                    $owner = $sub->restaurant->users()->where('role', 'owner')->first(); // Pivot where
                    if (! $owner) {
                        // Fallback: Just get first user attached
                        $owner = $sub->restaurant->users()->first();
                    }
                }

                if ($owner) {
                    $commService->sendNotification($warningTemplate, $owner, [
                        'restaurant_name' => $sub->restaurant->name,
                        'days_remaining' => $days,
                        'expiry_date' => $sub->ends_at->format('Y-m-d'),
                        'link' => route('plans.index'), // route() might fail in console if no base URL set in .env
                    ]);
                    $this->info("Sent warning to {$owner->email}");
                }
            }
        }

        // 2. Check for Expired Today
        $expiredSubs = Subscription::where('status', 'active')
            ->where('ends_at', '<', Carbon::now())
            ->get();

        foreach ($expiredSubs as $sub) {
            // Mark as expired
            $sub->update(['status' => 'expired']);

            // Notify
            $owner = null;
            if ($sub->restaurant) {
                $owner = $sub->restaurant->users()->where('role', 'owner')->first() ?? $sub->restaurant->users()->first();
            }

            if ($owner) {
                $commService->sendNotification('subscription_expired', $owner, [
                    'restaurant_name' => $sub->restaurant->name,
                    'expiry_date' => $sub->ends_at->format('Y-m-d'),
                    'link' => url('/plans'), // safer than route() in console sometimes
                ]);
                $this->info("Expired sub for {$owner->email}");
            }
        }

        $this->info('Done.');
    }
}
