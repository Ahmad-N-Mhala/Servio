<?php

namespace App\Console\Commands;

use App\Mail\ExpiryWarningMail;
use App\Models\IngredientBatch;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendExpiryReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:check-expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check inventory batches for expiry reminders and send emails';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting inventory expiry check...');

        // Find batches that:
        // 1. Have stock remaining
        // 2. Have an expiry date
        // 3. Have a reminder configured (days and user)
        $batches = IngredientBatch::where('quantity_remaining', '>', 0)
            ->whereNotNull('expiration_date')
            ->whereNotNull('reminder_days_before')
            ->with(['ingredient.restaurant'])
            ->get();

        $count = 0;

        foreach ($batches as $batch) {
            $expiryDate = Carbon::parse($batch->expiration_date)->startOfDay();
            $today = Carbon::now()->startOfDay();
            $reminderDate = $expiryDate->copy()->subDays($batch->reminder_days_before);

            // Check if TODAY is the reminder day
            // We use isSameDay to ensure we only send once (assuming command runs daily)
            // Alternatively, we could add a flag 'reminder_sent' to the batch if we wanted to be safer.
            // For now, based on requirements, sending on the reminder date is sufficient.
            if ($today->isSameDay($reminderDate)) {
                $recipient = null;
                $restaurant = $batch->ingredient->restaurant ?? null;

                // Priority: Specific User assigned to Batch
                if ($batch->reminder_user_id) {
                    $user = User::find($batch->reminder_user_id);
                    if ($user) {
                        $recipient = $user;
                        $this->info("Sending reminder for Batch {$batch->batch_number} (Item: {$batch->ingredient_id}) to User: {$user->email}");
                    }
                }

                // Fallback: Restaurant Notification Email
                if (! $recipient && $restaurant && ! empty($restaurant->notification_email)) {
                    $recipient = $restaurant->notification_email;
                    $this->info("Sending reminder for Batch {$batch->batch_number} (Item: {$batch->ingredient_id}) to Restaurant Email: {$recipient}");
                }

                if ($recipient) {
                    try {
                        // Prepare data for dynamic template
                        $data = [
                            'batch_number' => $batch->batch_number,
                            'ingredient_name_en' => $batch->ingredient->name['en'] ?? '',
                            'ingredient_name_ar' => $batch->ingredient->name['ar'] ?? ($batch->ingredient->name['en'] ?? ''),
                            'quantity_remaining' => $batch->quantity_remaining.' '.($batch->ingredient->unit ?? ''), // Fixed access to unit
                            'days_remaining' => $batch->reminder_days_before,
                            'expiry_date' => Carbon::parse($batch->expiration_date)->format('Y-m-d'),
                            'restaurant_id' => $restaurant ? $restaurant->id : null, // Pass context if string recipient
                        ];

                        $commService = app(\App\Services\CommunicationService::class);
                        $sent = $commService->sendNotification('inventory_expiry_warning', $recipient, $data);

                        if (! $sent) {
                            $emailTarget = ($recipient instanceof User) ? $recipient->email : $recipient;
                            // Fallback to hardcoded Mailable
                            Mail::to($emailTarget)->send(new ExpiryWarningMail($batch, $batch->reminder_days_before));

                            // MANUAL LOG
                            \App\Services\CommunicationService::log([
                                'restaurant_id' => $restaurant ? (string) $restaurant->id : null,
                                'recipient' => $emailTarget,
                                'type' => 'email',
                                'status' => 'sent',
                                'subject' => 'Inventory Expiry Warning',
                                'message' => "Batch {$batch->batch_number} for {$batch->ingredient->name['en']} is expiring soon.",
                            ]);
                        }

                        $count++;
                    } catch (\Exception $e) {
                        $emailTarget = ($recipient instanceof User) ? $recipient->email : $recipient;
                        $this->error("Failed to mail {$emailTarget}: ".$e->getMessage());

                        // Log Failure
                        \App\Services\CommunicationService::log([
                            'restaurant_id' => $restaurant ? (string) $restaurant->id : null,
                            'recipient' => $emailTarget,
                            'type' => 'email',
                            'status' => 'failed',
                            'subject' => 'Inventory Expiry Warning',
                            'message' => "Batch {$batch->batch_number} for {$batch->ingredient->name['en']} is expiring soon.",
                            'error_message' => $e->getMessage(),
                        ]);
                    }
                } else {
                    $this->warn("No recipient found (User or Restaurant Email) for Batch {$batch->batch_number}");
                }
            }
        }

        $this->info("Expiry check complete. Sent {$count} reminders.");
    }
}
