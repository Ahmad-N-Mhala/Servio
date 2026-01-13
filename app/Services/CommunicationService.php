<?php

namespace App\Services;

use App\Models\CommunicationTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CommunicationService
{
    /**
     * Send a notification based on a trigger event or specific template.
     *
     * @param string|CommunicationTemplate $trigger Notification event or specific template
     * @param User $user
     * @param array $data Additional data for replacement (e.g., link, restaurant_name)
     * @return bool True if a custom template was used, False if fallback logic should be used.
     */
    public function sendNotification(string|CommunicationTemplate $trigger, User $user, array $data = []): bool
    {
        $template = null;

        if ($trigger instanceof CommunicationTemplate) {
            $template = $trigger;
        } else {
            // Find active system template for this event
            $template = CommunicationTemplate::where('trigger_event', $trigger)
                ->whereNull('restaurant_id') // System level
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->where('channels', 'like', '%"email"%')
                        ->orWhere('channels', 'email');
                })
                ->first();
        }

        if (!$template) {
            return false; // Let caller handle fallback (standard Hardcoded Mailable)
        }

        // Determine Locale
        $restaurant = null;
        if ($template->restaurant_id) {
            $restaurant = $template->restaurant;
        } else {
            // For system templates, try to find restaurant from user context if possible
            $restaurant = $user->currentRestaurant();
        }

        $locale = $restaurant ? ($restaurant->locale ?? 'en') : 'en';

        // Process Content Variables
        // Pick Subject
        $subjectText = $template->{"subject_{$locale}"} ?? $template->subject ?? '';
        if (!$subjectText && $locale !== 'en') {
            $subjectText = $template->subject_en ?? $template->subject ?? '';
        }

        // Pick Content
        $contentText = $template->{"content_{$locale}"} ?? $template->content ?? '';
        if (!$contentText && $locale !== 'en') {
            $contentText = $template->content_en ?? $template->content ?? '';
        }

        $subject = $this->replaceVariables($subjectText, $user, $data);
        $content = $this->replaceVariables($contentText, $user, $data);

        // Send Generic Email
        try {
            Mail::to($user->email)->send(new \App\Mail\GenericSystemEmail($subject, $content));

            // Log the communication
            \App\Models\CommunicationLog::create([
                'restaurant_id' => $restaurant ? $restaurant->id : null,
                'communication_template_id' => $template->id,
                'recipient' => $user->email,
                'type' => 'email',
                'status' => 'sent',
                'message' => substr($content, 0, 1000),
                'sent_at' => now(),
            ]);

            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("System Email failed: " . $e->getMessage());

            \App\Models\CommunicationLog::create([
                'restaurant_id' => $restaurant ? $restaurant->id : null,
                'communication_template_id' => $template->id,
                'recipient' => $user->email,
                'type' => 'email',
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'sent_at' => now(),
            ]);

            return false;
        }
    }

    protected function replaceVariables(string $text, User $user, array $data): string
    {
        $vars = [
            '{{ name }}' => $user->name,
            '{{ email }}' => $user->email,
            '{{ link }}' => $data['link'] ?? '#',
            '{{ restaurant_name }}' => $data['restaurant_name'] ?? config('app.name'),
            '{{ owner_email }}' => $data['owner_email'] ?? $user->email,
            // Only expose password if explicitly passed (e.g. new account creation)
            '{{ owner_password }}' => $data['owner_password'] ?? '********',
            '{{ expiry_date }}' => $data['expiry_date'] ?? '',
            '{{ plan_name }}' => $data['plan_name'] ?? '',
            '{{ batch_number }}' => $data['batch_number'] ?? '',
            '{{ ingredient_name_en }}' => $data['ingredient_name_en'] ?? '',
            '{{ ingredient_name_ar }}' => $data['ingredient_name_ar'] ?? '',
            '{{ quantity_remaining }}' => $data['quantity_remaining'] ?? '',
            '{{ days_remaining }}' => $data['days_remaining'] ?? '',
            '{{ current_stock }}' => $data['current_stock'] ?? '',
            '{{ reorder_level }}' => $data['reorder_level'] ?? '',
        ];

        return str_replace(array_keys($vars), array_values($vars), $text);
    }
}
