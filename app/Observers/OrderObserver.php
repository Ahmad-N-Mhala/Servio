<?php

namespace App\Observers;

use App\Models\Order;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        $this->processTriggers($order, 'order_created');
    }

    public function updated(\App\Models\Order $order): void
    {
        if ($order->wasChanged('status')) {
            if ($order->status === 'completed') {
                $this->processTriggers($order, 'order_completed');
                $this->processTriggers($order, 'order_completed_feedback');
            } elseif ($order->status === 'cancelled') {
                $this->processTriggers($order, 'order_cancelled');
            }
        }

        // Revert loyalty points if order is no longer in "Paid" state
        $isEligible = $order->payment_status === 'paid';
        if ($order->points_earned > 0 && !$isEligible) {
            app(\App\Services\LoyaltyService::class)->revertOrderPoints($order);
        }
    }

    private function processTriggers(Order $order, string $event)
    {
        if (!$order->customer_id)
            return;

        $restaurant = $order->restaurant;

        // Find matching automation rules
        $rules = \App\Models\CommunicationTemplate::where('restaurant_id', (string) $restaurant->id)
            ->where('is_active', true)
            ->where('trigger_event', $event)
            ->get();

        foreach ($rules as $rule) {
            // Check Conditions
            if (!$this->checkConditions($rule, $order)) {
                continue;
            }

            // Generate Feedback Link (if needed for completion)
            $feedbackLink = route('public.feedback.create', [
                'identifier' => $restaurant->slug,
                'order_id' => $order->id,
                'customer_id' => $order->customer_id
            ]);

            $variables = [
                'feedback_link' => $feedbackLink,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer->name ?? $order->customer_name,
                'restaurant_name' => $restaurant->name
            ];

            // Determine Timing and Dispatch
            $this->dispatchWithTiming($rule, $order->customer, $variables);
        }
    }

    private function dispatchWithTiming($rule, $customer, $variables)
    {
        $delay = null;

        if ($rule->timing_type === 'after') {
            $days = (int) ($rule->timing_days ?? 0);
            $time = $rule->timing_time ?? '12:00';

            $delayDate = now()->addDays($days);
            $timeParts = explode(':', $time);
            if (count($timeParts) === 2) {
                $delayDate->setTime((int) $timeParts[0], (int) $timeParts[1], 0);
            }

            if ($delayDate->isPast()) {
                $delayDate = now();
            }
            $delay = $delayDate;

        } elseif ($rule->timing_type === 'custom_delay' && !empty($rule->conditions['delay_unit'])) {
            $delayVal = (int) ($rule->conditions['delay_val'] ?? 1);
            $unit = $rule->conditions['delay_unit'];
            $delayDate = now();

            if ($unit === 'minutes') {
                $delayDate->addMinutes($delayVal);
            } elseif ($unit === 'hours') {
                $delayDate->addHours($delayVal);
            } elseif ($unit === 'days') {
                $delayDate->addDays($delayVal);
            }
            $delay = $delayDate;
        }

        $job = \App\Jobs\SendCustomerCommunicationJob::dispatch($rule, $customer, $variables);
        if ($delay) {
            $job->delay($delay);
        }
    }

    private function checkConditions($rule, $order): bool
    {
        $conditions = $rule->conditions ?? [];

        if (!empty($conditions['min_order_amount']) && $order->total_amount < $conditions['min_order_amount']) {
            return false;
        }

        if (!empty($conditions['min_orders_count'])) {
            // Count past orders
            $count = \App\Models\Order::where('customer_id', $order->customer_id)
                ->where('restaurant_id', $order->restaurant_id)
                ->where('status', 'completed')
                ->count();

            if ($count < $conditions['min_orders_count']) {
                return false;
            }
        }

        if (!empty($conditions['loyalty_tier'])) {
            if ($order->customer->loyalty_tier !== $conditions['loyalty_tier']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }
}
