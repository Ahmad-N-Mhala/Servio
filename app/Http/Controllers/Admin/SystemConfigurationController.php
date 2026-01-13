<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
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
        ]);

        foreach ($data as $key => $value) {
            SystemConfiguration::set($key, $value);
        }

        return back()->with('success', 'System configurations updated successfully.');
    }

    public function testEmail(Request $request)
    {
        $email = $request->input('email');
        if (!$email) {
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

            return back()->with('success', 'Test email sent successfully to ' . $email);
        } catch (\Exception $e) {
            Log::error('Test Email Failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }

    public function testSms(Request $request)
    {
        $phone = $request->input('phone');
        if (!$phone) {
            return back()->with('error', 'Please provide a phone number for testing.');
        }

        try {
            app(\App\Services\SmsService::class)->send($phone, "Servio SMS Integration Test Successful.");
            return back()->with('success', 'Test SMS sent successfully to ' . $phone);
        } catch (\Exception $e) {
            Log::error('Test SMS Failed: ' . $e->getMessage());
            return back()->with('error', 'Test SMS Failed: ' . $e->getMessage());
        }
    }
}
