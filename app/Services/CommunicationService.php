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

        // Process Content Variables
        $subject = $this->replaceVariables($template->subject ?? '', $user, $data);
        $content = $this->replaceVariables($template->content ?? '', $user, $data);

        // Send Generic Email
        Mail::to($user->email)->send(new \App\Mail\GenericSystemEmail($subject, $content));

        return true;
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
        ];

        return str_replace(array_keys($vars), array_values($vars), $text);
    }
}
