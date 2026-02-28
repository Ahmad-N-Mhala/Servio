<?php

namespace App\Services;

use App\Models\CommunicationTemplate;
use App\Models\Customer;
use App\Models\CommunicationLog;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class CustomerCommunicationService
{
    public static function send(CommunicationTemplate $template, Customer $customer, array $data = []): array
    {
        $channels = $template->channels ?? [];

        // Ensure we find the right restaurant (System templates may have null restaurant_id)
        $restaurant = $template->restaurant ?? $customer->restaurant ?? \App\Models\Restaurant::find((string) $customer->restaurant_id);

        // Enrich data with common variables
        $data = array_merge([
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone,
            'restaurant_name' => $restaurant->name ?? config('app.name'),
        ], $data);

        // Enrich data with Reward Configuration if available
        if (!empty($template->reward_config)) {
            $config = $template->reward_config;
            $data['reward_type'] = $config['reward_type'] ?? '';
            $data['reward_value'] = $config['reward_value'] ?? '';
            $data['points_required'] = $config['points_required'] ?? '';

            if ($data['reward_type'] === 'discount_percentage') {
                $data['discount_percentage'] = ($config['reward_value'] ?? 0) . '%';
            }

            // Reflection of Menu Items
            if (!empty($config['menu_item_ids'])) {
                $itemIds = (array) $config['menu_item_ids'];
                $items = \App\Models\MenuItem::whereIn('id', $itemIds)->get();

                $locale = $restaurant->locale ?? 'en';
                $itemNames = $items->map(function ($item) use ($locale) {
                    // Safety check if it's not hydrated as an Eloquent model with Translatable trait
                    if (method_exists($item, 'getTranslation')) {
                        return $item->getTranslation('name', $locale) ?? $item->name;
                    }
                    return is_array($item->name) ? ($item->name[$locale] ?? array_values($item->name)[0]) : $item->name;
                })->implode(', ');

                $data['reward_item_name'] = $itemNames;
                $data['selected_items'] = $itemNames;
            }
        }

        // Add more Customer specific data
        $data['loyalty_tier'] = $customer->loyalty_tier ?? 'bronze';
        $data['current_points'] = $customer->current_points ?? 0;

        $data['birth_date'] = $customer->birth_date ? \Illuminate\Support\Carbon::parse($customer->birth_date)->toDateString() : '';
        $data['last_order_date'] = $customer->last_order_at ? \Illuminate\Support\Carbon::parse($customer->last_order_at)->toDateString() : '';

        $results = [];
        foreach ($channels as $channel) {
            if ($channel === 'email') {
                $results['email'] = self::sendEmail($template, $customer, $data);
            } elseif ($channel === 'sms') {
                $results['sms'] = self::sendSms($template, $customer, $data);
            }
        }
        return $results;
    }

    private static function sendEmail($template, $customer, $data)
    {
        if (!$customer->email) {
            Log::warning("CustomerCommunicationService: Customer " . $customer->id . " has no email address. Skipping email sending.");
            self::log($template, $customer, 'email', 'failed', 'Email skipped: Customer has no email address.', null, $template->restaurant_id ?? $customer->restaurant_id, 'Missing customer email');
            return;
        }

        // Ensure we find the right restaurant (System templates may have null restaurant_id)
        $restaurant = $template->restaurant ?? $customer->restaurant ?? \App\Models\Restaurant::find($customer->restaurant_id);

        $locale = $restaurant->locale ?? 'en';

        // Pick Subject
        $subject = $template->{"subject_{$locale}"} ?? $template->subject;
        if (!$subject && $locale !== 'en') {
            $subject = $template->subject_en ?? $template->subject;
        }

        // Pick Content
        $content = $template->{"content_{$locale}"} ?? $template->content;
        if (!$content && $locale !== 'en') {
            $content = $template->content_en ?? $template->content;
        }

        $subject = self::replaceVariables($subject, $data);
        $content = self::replaceVariables($content, $data);

        if (!$restaurant || $restaurant->email_balance <= 0) {
            Log::warning("Restaurant " . ($restaurant->id ?? 'Unknown') . " out of email credits or not found.");
            self::log($template, $customer, 'email', 'failed', $content, $subject, $restaurant->id ?? null, 'Insufficient balance or restaurant mismatch');
            return;
        }

        try {
            // Real Email Integration
            Mail::to($customer->email)->send(new \App\Mail\GenericSystemEmail($subject, $content));

            $restaurant->decrement('email_balance');
            self::log($template, $customer, 'email', 'sent', $content, $subject, $restaurant->id ?? null);
            return ['success' => true, 'status' => 'sent'];
        } catch (\Exception $e) {
            Log::error("Email failed: " . $e->getMessage());
            self::log($template, $customer, 'email', 'failed', $content, $subject, $restaurant->id ?? null, $e->getMessage());
            return ['success' => false, 'status' => 'failed', 'error' => $e->getMessage()];
        }
    }

    private static function sendSms($template, $customer, $data)
    {
        if (!$customer->phone) {
            Log::warning("CustomerCommunicationService: Customer " . $customer->id . " has no phone number. Skipping SMS sending.");
            self::log($template, $customer, 'sms', 'failed', 'SMS skipped: Customer has no phone number.', null, $template->restaurant_id ?? $customer->restaurant_id, 'Missing customer phone');
            return;
        }

        // Ensure we find the right restaurant (System templates may have null restaurant_id)
        $restaurant = $template->restaurant ?? $customer->restaurant ?? \App\Models\Restaurant::find((string) $customer->restaurant_id);

        $locale = $restaurant->locale ?? 'en';

        // Pick SMS Content
        $content = $template->{"sms_content_{$locale}"} ?? $template->sms_content ?? $template->content;
        if (!$content) {
            $content = $template->{"content_{$locale}"} ?? ($locale === 'en' ? $template->content_en : $template->content_ar);
        }

        if (!$content && $locale !== 'en') {
            $content = $template->sms_content_en ?? $template->content_en ?? $template->sms_content ?? $template->content;
        }

        $content = self::replaceVariables($content, $data);

        // Use Centralized SmsService
        $result = app(\App\Services\SmsService::class)->send($customer->phone, $content);

        // Always log the result
        self::log(
            $template,
            $customer,
            'sms',
            $result['status'],
            $content,
            null,
            $restaurant->id ?? (string) $customer->restaurant_id,
            $result['error']
        );

        return $result;
    }

    private static function replaceVariables(?string $text, array $data): string
    {
        if (!$text)
            return '';

        // Add curly braces to keys for replacement
        $search = [];
        $replace = [];
        foreach ($data as $key => $value) {
            if (is_array($value) || is_object($value))
                continue;

            $search[] = '{{' . $key . '}}';
            $search[] = '{{ ' . $key . ' }}';
            $replace[] = (string) $value;
            $replace[] = (string) $value;
        }

        return str_replace($search, $replace, $text);
    }

    private static function log($template, $customer, $type, $status, $message = null, $subject = null, $forceRestaurantId = null, $errorMessage = null)
    {
        // Root Fix: Use data_get and explicit casting to ensure restaurant_id is never NULL if available
        $restaurantId = $forceRestaurantId ?? data_get($template, 'restaurant_id') ?? data_get($customer, 'restaurant_id');
        $restaurantId = $restaurantId ? (string) $restaurantId : null;

        CommunicationLog::create([
            'restaurant_id' => $restaurantId,
            'communication_template_id' => $template ? (string) $template->id : null,
            'recipient' => $type === 'email' ? ($customer->email ?? 'N/A') : ($customer->phone ?? 'N/A'),
            'type' => $type, // sms or email
            'status' => $status,
            'subject' => $subject,
            'message' => $type === 'email' ? $message : substr($message ?? '', 0, 1000), // truncate only SMS/short logs
            'error_message' => $errorMessage,
            'sent_at' => now(),
        ]);
    }
}
