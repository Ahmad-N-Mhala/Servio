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
        $driver = config('services.sms.driver', 'log');

        try {
            switch ($driver) {
                case 'twilio':
                    self::sendViaTwilio($customer->phone, $content);
                    break;
                case 'unifonic':
                    self::sendViaUnifonic($customer->phone, $content);
                    break;
                case 'sms_ae':
                    self::sendViaSmsAe($customer->phone, $content);
                    break;
                default:
                    Log::info("SMS (Log Driver) to {$customer->phone}: {$content}");
                    break;
            }

            $restaurant->decrement('sms_balance');
            self::log($template, $customer, 'sms', 'sent', $content);
        } catch (\Exception $e) {
            Log::error("SMS failed via {$driver}: " . $e->getMessage());
            self::log($template, $customer, 'sms', 'failed', $e->getMessage());
        }
    }

    private static function sendViaTwilio($to, $message)
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.from');

        if (!$sid || !$token || !$from) {
            throw new \Exception("Twilio credentials missing");
        }

        $response = \Illuminate\Support\Facades\Http::asForm()
            ->withBasicAuth($sid, $token)
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'To' => $to,
                'From' => $from,
                'Body' => $message,
            ]);

        if (!$response->successful()) {
            throw new \Exception("Twilio Error: " . $response->body());
        }
    }

    private static function sendViaUnifonic($to, $message)
    {
        $apiKey = config('services.unifonic.api_key');
        $senderId = config('services.unifonic.sender_id');

        if (!$apiKey) {
            throw new \Exception("Unifonic API Key missing");
        }

        $response = \Illuminate\Support\Facades\Http::post("https://el.cloud.unifonic.com/rest/SMS/Messages", [
            'AppSid' => $apiKey,
            'SenderID' => $senderId,
            'Recipient' => $to,
            'Body' => $message,
        ]);

        if (!$response->successful()) {
            throw new \Exception("Unifonic Error: " . $response->body());
        }
    }

    private static function sendViaSmsAe($to, $message)
    {
        $user = config('services.sms_ae.username');
        $pass = config('services.sms_ae.password');
        $sender = config('services.sms_ae.sender_id');

        if (!$user || !$pass) {
            throw new \Exception("SMS.ae credentials missing");
        }

        $response = \Illuminate\Support\Facades\Http::get("https://www.sms.ae/api/http/send.aspx", [
            'username' => $user,
            'password' => $pass,
            'recipient' => $to,
            'sender' => $sender,
            'message' => $message,
        ]);

        if (!$response->successful()) {
            throw new \Exception("SMS.ae Error: " . $response->body());
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
