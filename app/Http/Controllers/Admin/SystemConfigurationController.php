<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class SystemConfigurationController extends Controller
{
    public function index()
    {
        $configurations = SystemConfiguration::all()->pluck('value', 'key');

        return Inertia::render('Admin/Integrations/System', [
            'configurations' => $configurations,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'mail_host' => 'nullable|string',
            'mail_port' => 'nullable|numeric',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|in:tls,ssl,null',
            'mail_from_address' => 'nullable|email',
            'mail_from_name' => 'nullable|string',
            'registration_email' => 'nullable|email',
            'sms_provider' => 'nullable|string',
            'sms_sid' => 'nullable|string',
            'sms_token' => 'nullable|string',
            'sms_from' => 'nullable|string',
            'support_email' => 'nullable|email',
            'support_phone' => 'nullable|string',
        ]);

        foreach ($data as $key => $value) {
            SystemConfiguration::set($key, $value);
        }

        return back()->with('success', 'System configurations updated successfully.');
    }

    public function testEmail(Request $request)
    {
        $email = $request->input('email');
        if (! $email) {
            return back()->with('error', 'Please provide an email address for testing.');
        }

        // Force config based on current settings (even if not yet reloaded by provider)
        // Actually, let's rely on DB settings which should be loaded if we saved them.
        // But AppServiceProvider runs on boot. Middleware?
        // We might need to manually set config here for the test to use LATEST values immediately.

        config([
            'mail.from.address' => SystemConfiguration::get('mail_from_address', config('mail.from.address')),
            'mail.from.name' => SystemConfiguration::get('mail_from_name', config('mail.from.name')),
        ]);

        try {
            Mail::raw('This is a test email from Servio Integration Settings.', function ($message) use ($email) {
                $message->to($email)
                    ->subject('Servio Integration Test Email');
            });

            return back()->with('success', 'Test email sent successfully to '.$email);
        } catch (\Exception $e) {
            Log::error('Test Email Failed: '.$e->getMessage());

            return back()->with('error', 'Failed to send test email: '.$e->getMessage());
        }
    }

    public function testSms(Request $request)
    {
        $phone = $request->input('phone');
        if (! $phone) {
            return back()->with('error', 'Please provide a phone number for testing.');
        }

        try {
            app(\App\Services\SmsService::class)->send($phone, 'Servio SMS Integration Test Successful.');

            return back()->with('success', 'Test SMS sent successfully to '.$phone);
        } catch (\Exception $e) {
            Log::error('Test SMS Failed: '.$e->getMessage());

            return back()->with('error', 'Test SMS Failed: '.$e->getMessage());
        }
    }

    /**
     * Download database backup as a gzipped archive.
     */
    public function downloadBackup()
    {
        $connection = config('database.default');

        if ($connection !== 'mongodb') {
            return back()->with('error', 'Database backup is only supported for MongoDB.');
        }

        $config = config('database.connections.mongodb');
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 27017;
        $database = $config['database'] ?? 'servio';
        $username = $config['username'] ?? '';
        $password = $config['password'] ?? '';
        $authDatabase = $config['options']['database'] ?? 'admin';

        $cmd = ['mongodump', '--host=' . $host, '--port=' . $port, '--db=' . $database];

        if (!empty($username)) {
            $cmd[] = '--username=' . $username;
            if (!empty($password)) {
                $cmd[] = '--password=' . $password;
            }
            if (!empty($authDatabase)) {
                $cmd[] = '--authenticationDatabase=' . $authDatabase;
            }
        }

        $cmd[] = '--archive';
        $cmd[] = '--gzip';

        try {
            return response()->stream(function () use ($cmd) {
                $process = new \Symfony\Component\Process\Process($cmd);
                $process->setTimeout(600); // 10 minutes timeout
                $process->start();

                foreach ($process->getIterator(\Symfony\Component\Process\Process::ITER_SKIP_ERR) as $buffer) {
                    echo $buffer;
                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();
                }

                if (!$process->isSuccessful()) {
                    Log::error('Database backup execution failed: ' . $process->getErrorOutput());
                }
            }, 200, [
                'Content-Type' => 'application/gzip',
                'Content-Disposition' => 'attachment; filename="servio_backup_' . date('Y-m-d_H-i-s') . '.gz"',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ]);
        } catch (\Exception $e) {
            Log::error('Database backup initialization failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to start backup: ' . $e->getMessage());
        }
    }
}

