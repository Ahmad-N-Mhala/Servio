<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Staff;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class StaffInviteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Staff $staff,
        public string $email
    ) {}

    public function handle(): void
    {
        $token = Str::random(64);
        
        $this->staff->update([
            'invited_at' => now(),
        ]);

        $inviteUrl = url("/staff/invite/{$token}");

        Mail::raw("You have been invited to join RestaurFy. Click here to accept: {$inviteUrl}", function ($message) {
            $message->to($this->email)
                    ->subject('RestaurFy Staff Invitation');
        });
    }
}

