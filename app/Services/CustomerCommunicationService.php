<?php

namespace App\Services;

use App\Models\CommunicationTemplate;
use App\Models\Customer;
use App\Models\CommunicationLog;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class CustomerCommunicationService
{
    public static function send(CommunicationTemplate $template, Customer $customer, array $data = []): void
    {
        $channels = $template->channels ?? [];

        foreach ($channels as $channel) {
            if ($channel === 'email') {
                self::sendEmail($template, $customer, $data);
            } elseif ($channel === 'sms') {
                self::sendSms($template, $customer, $data);
            }
        }
    }

    private static function sendEmail($template, $customer, $data)
    {
        if (!$customer->email)
            return;

        $restaurant = $template->restaurant;
        if ($restaurant->email_balance <= 0) {
            Log::warning("Restaurant {$restaurant->id} out of email credits.");
            self::log($template, $customer, 'email', 'failed', 'Insufficient balance');
            return;
        }

        $subject = self::replaceVariables($template->subject, $data);
        $content = self::replaceVariables($template->content, $data);

        try {
            // Real Email Integration
            Mail::to($customer->email)->send(new \App\Mail\GenericSystemEmail($subject, $content));

            $restaurant->decrement('email_balance');
            self::log($template, $customer, 'email', 'sent', $content);
        } catch (\Exception $e) {
            Log::error("Email failed: " . $e->getMessage());
            self::log($template, $customer, 'email', 'failed', $e->getMessage());
        }
    }

    private static function sendSms($template, $customer, $data)
    {
        if (!$customer->phone)
            return;

        $restaurant = $template->restaurant;
        if ($restaurant->sms_balance <= 0) {
            Log::warning("Restaurant {$restaurant->id} out of SMS credits.");
            self::log($template, $customer, 'sms', 'failed', 'Insufficient balance');
            return;
        }

        $content = self::replaceVariables($template->sms_content, $data);

        try {
            // Use Centralized SmsService
            app(\App\Services\SmsService::class)->send($customer->phone, $content);

            $restaurant->decrement('sms_balance');
            self::log($template, $customer, 'sms', 'sent', $content);
        } catch (\Exception $e) {
            Log::error("SMS failed: " . $e->getMessage());
            self::log($template, $customer, 'sms', 'failed', $e->getMessage());
        }
    }

    private static function replaceVariables($text, $data)
    {
        if (!$text)
            return '';

        foreach ($data as $key => $value) {
            $text = str_replace('{{' . $key . '}}', $value, $text);
            $text = str_replace('{{ ' . $key . ' }}', $value, $text); // handle spaces
        }
        return $text;
    }

    private static function log($template, $customer, $type, $status, $message = null)
    {
        CommunicationLog::create([
            'restaurant_id' => $template->restaurant_id,
            'communication_template_id' => $template->id,
            'recipient' => $type === 'email' ? $customer->email : $customer->phone,
            'type' => $type, // sms or email
            'status' => $status,
            'message' => substr($message, 0, 1000), // truncate if too long
            'sent_at' => now(),
        ]);
    }
}
