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
        //
    }

    public function updated(\App\Models\Order $order): void
    {
        // Check if status changed to 'completed'
        if ($order->isDirty('status') && $order->status === 'completed' && $order->customer_id) {

            $restaurant = $order->restaurant;

            // Find 'Order Completed' automation rules
            $rules = \App\Models\CommunicationTemplate::where('restaurant_id', $restaurant->id)
                ->where('is_active', true)
                ->whereIn('trigger_event', ['order_completed', 'order_completed_feedback'])
                ->get();

            foreach ($rules as $rule) {
                // Check Conditions
                if (!$this->checkConditions($rule, $order)) {
                    continue;
                }

                // Generate Feedback Link
                $feedbackLink = route('public.feedback.create', [
                    'identifier' => $restaurant->slug,
                    'order_id' => $order->id,
                    'customer_id' => $order->customer_id
                ]);

                $variables = [
                    'feedback_link' => $feedbackLink,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer->name,
                    'restaurant_name' => $restaurant->name
                ];

                // Determine Delay and Dispatch
                if ($rule->timing_type === 'custom_delay' && !empty($rule->conditions['delay_unit'])) {
                    $delayVal = (int) ($rule->conditions['delay_val'] ?? 1);
                    $unit = $rule->conditions['delay_unit'];
                    $delay = now();

                    if ($unit === 'minutes') {
                        $delay->addMinutes($delayVal);
                    } elseif ($unit === 'hours') {
                        $delay->addHours($delayVal);
                    } elseif ($unit === 'days') {
                        $delay->addDays($delayVal);
                    }

                    \App\Jobs\SendCustomerCommunicationJob::dispatch($rule, $order->customer, $variables)->delay($delay);

                } elseif ($rule->timing_type === 'delay_1_hour') {
                    // Legacy support
                    $delay = now()->addHour();
                    \App\Jobs\SendCustomerCommunicationJob::dispatch($rule, $order->customer, $variables)->delay($delay);
                } elseif ($rule->timing_type === 'delay_24_hours') {
                    // Legacy support
                    $delay = now()->addHours(24);
                    \App\Jobs\SendCustomerCommunicationJob::dispatch($rule, $order->customer, $variables)->delay($delay);
                } else {
                    // Send Immediately (Sync or Async without delay)
                    \App\Jobs\SendCustomerCommunicationJob::dispatch($rule, $order->customer, $variables);
                }
            }
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
