<?php

namespace App\Console\Commands;

use App\Models\CommunicationTemplate;
use App\Models\RestaurantSubscription;
use App\Models\User;
use App\Services\CommunicationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NotifyExpiringSubscriptions extends Command
{
    protected $signature = 'subscriptions:notify-expiring';
    protected $description = 'Send notifications for expiring subscriptions based on system templates';

    public function handle(CommunicationService $commService)
    {
        $this->info('Checking expiring subscriptions...');

        // 1. Find all system templates for subscription expiring
        $templates = CommunicationTemplate::whereNull('restaurant_id')
            ->where('trigger_event', 'subscription_expired') // We treat expiring as a timing variant of expired or specific key
            ->where('is_active', true)
            ->whereIn('timing_type', ['before', 'after', 'immediately'])
            ->get();

        // Also check if user preferred a specific key like 'subscription_warning'
        $warningTemplates = CommunicationTemplate::whereNull('restaurant_id')
            ->where('trigger_event', 'subscription_warning')
            ->where('is_active', true)
            ->get();

        $allTemplates = $templates->merge($warningTemplates);

        if ($allTemplates->isEmpty()) {
            $this->warn('No active system templates found for subscription notifications.');
            return;
        }

        foreach ($allTemplates as $template) {
            $days = (int) $template->timing_days;
            $type = $template->timing_type;

            // Check Time of Day (Ensures the hourly cron only sends at the right hour)
            $targetHour = $template->timing_time ? Carbon::parse($template->timing_time)->format('H') : '09';
            if (now()->format('H') !== $targetHour) {
                continue;
            }

            if ($type === 'before') {
                // Subscription ends in X days
                $targetDate = Carbon::today()->addDays($days);
                $subscriptions = RestaurantSubscription::where('status', 'active')
                    ->whereDate('ends_at', $targetDate)
                    ->get();
            } elseif ($type === 'after') {
                // Subscription ended X days ago
                $targetDate = Carbon::today()->subDays($days);
                $subscriptions = RestaurantSubscription::whereIn('status', ['expired', 'cancelled'])
                    ->whereDate('ends_at', $targetDate)
                    ->get();
            } elseif ($type === 'immediately') {
                // Actually ends today
                $targetDate = Carbon::today();
                $subscriptions = RestaurantSubscription::whereDate('ends_at', $targetDate)
                    ->get();
            } else {
                continue;
            }

            foreach ($subscriptions as $sub) {
                // Check if already notified for THIS template today
                $alreadyLogged = \App\Models\CommunicationLog::where('communication_template_id', (string) $template->id)
                    ->where('restaurant_id', (string) $sub->restaurant_id)
                    ->whereDate('sent_at', Carbon::today())
                    ->exists();

                if (!$alreadyLogged) {
                    $this->notifyOwner($sub, $template, $commService);
                }
            }
        }

        $this->info('Done.');
    }

    protected function notifyOwner(RestaurantSubscription $subscription, CommunicationTemplate $template, CommunicationService $commService)
    {
        $restaurant = $subscription->restaurant;
        if (!$restaurant)
            return;

        // Find Owner
        $ownerPivot = DB::table('restaurant_user')
            ->where('restaurant_id', (string) $restaurant->id)
            ->where('role', 'owner')
            ->first();

        if (!$ownerPivot)
            return;

        $user = User::where('email', $ownerPivot->email)->first();
        if (!$user)
            return;

        $this->line("Notifying owner of {$restaurant->name} ({$user->email}) for template: {$template->name}");

        $commService->sendNotification($template, $user, [
            'restaurant_name' => $restaurant->name,
            'expiry_date' => $subscription->ends_at ? $subscription->ends_at->format('Y-m-d') : 'N/A',
            'plan_name' => $subscription->plan->name ?? 'Plan',
            'days_remaining' => $template->timing_days,
            'link' => route('plans.index'),
        ]);
    }
}
