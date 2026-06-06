<?php

namespace App\Observers;

class FeedbackObserver
{
    public function created(\App\Models\Feedback $feedback): void
    {
        // Find matching automation rules
        $topRule = \App\Models\CommunicationTemplate::where('restaurant_id', $feedback->restaurant_id)
            ->where('is_active', true)
            ->where('trigger_event', 'feedback_received')
            ->get()
            ->filter(function ($template) use ($feedback) {
                $conditions = $template->conditions ?? [];

                // Check Rating
                if (isset($conditions['min_rating']) && $feedback->rating < $conditions['min_rating']) {
                    return false;
                }
                if (isset($conditions['max_rating']) && $feedback->rating > $conditions['max_rating']) {
                    return false;
                }

                return true;
            })
            ->first(); // For simplicity, take the first matching rule, or loop them all

        if ($topRule && $feedback->customer) {
            $variables = [
                'customer_name' => $feedback->customer->name,
                'restaurant_name' => $feedback->restaurant->name ?? 'our restaurant',
                'rating' => $feedback->rating,
                'comment' => $feedback->comment ?? '',
            ];

            // Determine Delay and Dispatch
            if ($topRule->timing_type === 'after') {
                $days = (int) ($topRule->timing_days ?? 0);
                $time = $topRule->timing_time ?? '12:00';

                $delay = now()->addDays($days);

                $timeParts = explode(':', $time);
                if (count($timeParts) === 2) {
                    $delay->setTime((int) $timeParts[0], (int) $timeParts[1], 0);
                }

                if ($delay->isPast()) {
                    $delay = now();
                }

                \App\Jobs\SendCustomerCommunicationJob::dispatch($topRule, $feedback->customer, $variables)->delay($delay);
            } else {
                // Sync/Immediate
                \App\Jobs\SendCustomerCommunicationJob::dispatch($topRule, $feedback->customer, $variables);
            }

            // Award Feedback Points if configured in the rule
            if (! empty($topRule->conditions['feedback_points'])) {
                $points = (int) $topRule->conditions['feedback_points'];
                if ($points > 0) {
                    $lp = $feedback->customer->loyaltyPoints()->firstOrCreate([
                        'customer_id' => $feedback->customer->id,
                    ], [
                        'balance' => 0,
                        'total_earned' => 0,
                        'total_redeemed' => 0,
                    ]);

                    $lp->addPoints($points, "Points earned for feedback on Order #{$feedback->order->order_number}", $feedback->order_id);
                }
            }
        }
    }
}
