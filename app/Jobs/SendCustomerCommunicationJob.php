<?php

namespace App\Jobs;

use App\Models\CommunicationTemplate;
use App\Models\Customer;
use App\Services\CustomerCommunicationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCustomerCommunicationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $template;

    protected $customer;

    protected $variables;

    /**
     * Create a new job instance.
     */
    public function __construct(CommunicationTemplate $template, Customer $customer, array $variables = [])
    {
        $this->template = $template;
        $this->customer = $customer;
        $this->variables = $variables;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        CustomerCommunicationService::send($this->template, $this->customer, $this->variables);
    }
}
