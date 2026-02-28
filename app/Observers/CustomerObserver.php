<?php

namespace App\Observers;

use App\Models\Customer;
use App\Models\CommunicationTemplate;
use App\Jobs\SendCustomerCommunicationJob;

class CustomerObserver
{
    /**
     * Handle the Customer "created" event.
     */
    public function created(Customer $customer): void
    {
        // Trigger "registration" event
        $rules = CommunicationTemplate::where('restaurant_id', (string) $customer->restaurant_id)
            ->where('is_active', true)
            ->where('trigger_event', 'registration')
            ->get();

        foreach ($rules as $rule) {
            $variables = [
                'customer_name' => $customer->name,
                'restaurant_name' => $customer->restaurant->name ?? config('app.name'),
            ];

            $this->dispatchWithTiming($rule, $customer, $variables);
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
        }

        $job = SendCustomerCommunicationJob::dispatch($rule, $customer, $variables);
        if ($delay) {
            $job->delay($delay);
        }
    }

    /**
     * Handle the Customer "updated" event.
     */
    public function updated(Customer $customer): void
    {
        //
    }
}
