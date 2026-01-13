<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Send an SMS using the configured driver.
     *
     * @param string $to Recipient phone number
     * @param string $message Message content
     * @return void
     * @throws \Exception
     */
    public function send(string $to, string $message): void
    {
        $driver = config('services.sms.driver', 'log');

        switch ($driver) {
            case 'twilio':
                $this->sendViaTwilio($to, $message);
                break;
            case 'nexmo':
                $this->sendViaNexmo($to, $message);
                break;
            case 'unifonic':
                $this->sendViaUnifonic($to, $message);
                break;
            case 'sms_ae':
                $this->sendViaSmsAe($to, $message);
                break;
            default:
                // Fallback to log
                Log::info("SMS (Log Driver) to {$to}: {$message}");
                break;
        }
    }

    private function sendViaTwilio($to, $message)
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.from');

        if (!$sid || !$token || !$from) {
            throw new \Exception("Twilio credentials missing");
        }

        $response = Http::asForm()
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

    private function sendViaUnifonic($to, $message)
    {
        $apiKey = config('services.unifonic.api_key');
        $senderId = config('services.unifonic.sender_id');

        if (!$apiKey) {
            throw new \Exception("Unifonic API Key missing");
        }

        $response = Http::post("https://el.cloud.unifonic.com/rest/SMS/Messages", [
            'AppSid' => $apiKey,
            'SenderID' => $senderId,
            'Recipient' => $to,
            'Body' => $message,
        ]);

        if (!$response->successful()) {
            throw new \Exception("Unifonic Error: " . $response->body());
        }
    }

    private function sendViaSmsAe($to, $message)
    {
        $user = config('services.sms_ae.username');
        $pass = config('services.sms_ae.password');
        $sender = config('services.sms_ae.sender_id');

        if (!$user || !$pass) {
            throw new \Exception("SMS.ae credentials missing");
        }

        $response = Http::get("https://www.sms.ae/api/http/send.aspx", [
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

    private function sendViaNexmo($to, $message)
    {
        $key = config('services.nexmo.key');
        $secret = config('services.nexmo.secret');
        $from = config('services.nexmo.sms_from') ?? 'SERVIO';

        if (!$key || !$secret) {
            throw new \Exception("Nexmo credentials missing (Key/Secret).");
        }

        // Nexmo API
        $response = Http::asForm()->post("https://rest.nexmo.com/sms/json", [
            'api_key' => $key,
            'api_secret' => $secret,
            'to' => $to,
            'from' => $from,
            'text' => $message,
        ]);

        if (!$response->successful()) {
            throw new \Exception("Nexmo HTTP Error: " . $response->body());
        }

        $json = $response->json();
        if (isset($json['messages'][0]['status']) && $json['messages'][0]['status'] != '0') {
            throw new \Exception("Nexmo API Error: " . ($json['messages'][0]['error-text'] ?? 'Unknown error'));
        }
    }
}
