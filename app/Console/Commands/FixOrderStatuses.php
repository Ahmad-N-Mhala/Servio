<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;

class FixOrderStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:order-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix inconsistent order statuses (e.g. served + paid => completed)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Scanning for inconsistent orders...');

        $orders = Order::where('status', 'served')
            ->where('payment_status', 'paid')
            ->get();

        $count = $orders->count();
        if ($count === 0) {
            $this->info('No inconsistent orders found.');
            return;
        }

        $this->info("Found {$count} orders that are Served + Paid but not Completed.");

        if ($this->confirm('Do you want to fix them now?', true)) {
            $bar = $this->output->createProgressBar($count);
            $bar->start();

            foreach ($orders as $order) {
                $order->update([
                    'status' => 'completed',
                    'completed_at' => $order->updated_at ?? now(),
                ]);
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info('All orders fixed successfully!');
        }
    }
}
