<?php

namespace App\Console\Commands;

use App\Jobs\SendCustomerCommunicationJob;
use App\Models\CommunicationTemplate;
use App\Models\Customer;
use Illuminate\Console\Command;

class CheckCommunicationTriggers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-communication-triggers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks for scheduled communication triggers like birthdays and churn risk';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting communication trigger check...');

        $templates = CommunicationTemplate::where('is_active', true)
            ->whereIn('trigger_event', ['birthday', 'churn_risk'])
            ->get();

        foreach ($templates as $template) {
            /** @var CommunicationTemplate $template */
            $this->processTemplate($template);
        }

        $this->info('Finished communication trigger check.');
    }

    /**
     * @param  CommunicationTemplate  $template
     */
    private function processTemplate($template)
    {
        $this->info("Processing template: {$template->name} ({$template->trigger_event})");

        if ($template->trigger_event === 'birthday') {
            $this->processBirthday($template);
        } elseif ($template->trigger_event === 'churn_risk') {
            $this->processChurnRisk($template);
        }
    }

    /**
     * @param  CommunicationTemplate  $template
     */
    private function processBirthday($template)
    {
        // Birthday trigger logic
        // timing_type: 'immediately' (on birthday), 'before' (X days before), 'after' (X days after)
        $days = (int) ($template->timing_days ?? 0);
        $type = $template->timing_type ?? 'immediately';

        $targetDate = now();
        if ($type === 'before') {
            $targetDate = now()->addDays($days);
        } elseif ($type === 'after') {
            $targetDate = now()->subDays($days);
        }

        $month = $targetDate->month;
        $day = $targetDate->day;

        // Find customers with birthday on this month/day
        // Using MongoDB compatible query
        $customers = Customer::where('restaurant_id', $template->restaurant_id)
            ->where(function ($query) use ($month, $day) {
                // Check both birth_date and birthday fields
                $query->whereMonth('birth_date', $month)->whereDay('birth_date', $day)
                    ->orWhereMonth('birthday', $month)->whereDay('birthday', $day);
            })
            ->get();

        foreach ($customers as $customer) {
            /** @var Customer $customer */
            $this->dispatchCommunication($template, $customer);
        }
    }

    /**
     * @param  CommunicationTemplate  $template
     */
    private function processChurnRisk($template)
    {
        $conditions = $template->conditions ?? [];
        $daysInactivity = (int) ($conditions['days_since_last_order'] ?? 30);

        // Find customers who ordered exactly $daysInactivity days ago and haven't ordered since
        $targetDate = now()->subDays($daysInactivity)->startOfDay();
        $endDate = now()->subDays($daysInactivity)->endOfDay();

        $customers = Customer::where('restaurant_id', $template->restaurant_id)
            ->whereBetween('last_order_at', [$targetDate, $endDate])
            ->get();

        foreach ($customers as $customer) {
            /** @var Customer $customer */
            $this->dispatchCommunication($template, $customer);
        }
    }

    /**
     * @param  CommunicationTemplate  $template
     * @param  Customer  $customer
     */
    private function dispatchCommunication($template, $customer)
    {
        // Avoid sending the same template to the same customer on the same day
        $alreadySent = \App\Models\CommunicationLog::where('communication_template_id', (string) $template->id)
            ->where('recipient', $customer->email ?? $customer->phone)
            ->whereDate('sent_at', now()->toDateString())
            ->exists();

        if ($alreadySent) {
            return;
        }

        $variables = [
            'customer_name' => $customer->name,
            'restaurant_name' => $template->restaurant->name ?? config('app.name'),
        ];

        // If timing_time is set for scheduled triggers, we can delay the job
        $delay = null;
        if (! empty($template->timing_time)) {
            $timeParts = explode(':', $template->timing_time);
            if (count($timeParts) === 2) {
                $delay = now()->setTime((int) $timeParts[0], (int) $timeParts[1], 0);
                if ($delay->isPast()) {
                    // If time already passed today, send immediately or maybe it was intended for today?
                    // For background commands running daily, we usually want it at that specific time.
                    // If run at 1am, it delays to 9am.
                    $delay = $delay->isPast() ? now() : $delay;
                }
            }
        }

        $job = SendCustomerCommunicationJob::dispatch($template, $customer, $variables);
        if ($delay) {
            $job->delay($delay);
        }

        $this->info("Dispatched communication for customer: {$customer->name}");
    }
}
