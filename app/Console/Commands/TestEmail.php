<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {recipient?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email configuration by sending a test email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $recipient = $this->argument('recipient') ?? config('mail.from.address');

        $this->info('Testing email configuration...');
        $this->info('Recipient: ' . $recipient);
        $this->info('From: ' . config('mail.from.address'));
        $this->info('Mailer: ' . config('mail.default'));
        $this->info('Host: ' . config('mail.mailers.smtp.host'));
        $this->info('Port: ' . config('mail.mailers.smtp.port'));
        $this->newLine();

        try {
            Mail::raw('This is a test email from Servio. If you receive this, your email configuration is working correctly!', function ($message) use ($recipient) {
                $message->to($recipient)
                    ->subject('Servio Email Configuration Test');
            });

            $this->info('✅ Test email sent successfully!');
            $this->info('Please check the inbox for: ' . $recipient);
            $this->newLine();
            $this->info('If you don\'t receive the email:');
            $this->warn('1. Check your spam/junk folder');
            $this->warn('2. Verify you\'re using a Gmail App Password (not regular password)');
            $this->warn('3. Check storage/logs/laravel.log for errors');
            $this->warn('4. Ensure 2-Step Verification is enabled on your Gmail account');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Failed to send test email!');
            $this->error('Error: ' . $e->getMessage());
            $this->newLine();
            $this->warn('Common issues:');
            $this->warn('1. Not using Gmail App Password - regular passwords don\'t work');
            $this->warn('2. 2-Step Verification not enabled on Gmail account');
            $this->warn('3. Incorrect SMTP settings in .env file');
            $this->warn('4. Config cache needs clearing: php artisan config:clear');
            $this->newLine();
            $this->info('To generate Gmail App Password:');
            $this->info('1. Go to https://myaccount.google.com/');
            $this->info('2. Security > 2-Step Verification > App passwords');
            $this->info('3. Generate password for "Mail" > "Other (Servio)"');
            $this->info('4. Update MAIL_PASSWORD in .env with the 16-char password');
            $this->info('5. Run: php artisan config:clear');

            return Command::FAILURE;
        }
    }
}
