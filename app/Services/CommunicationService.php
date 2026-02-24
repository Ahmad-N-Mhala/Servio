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
     * @param User|string $recipient User object or email string
     * @param array $data Additional data for replacement (e.g., link, restaurant_name)
     * @return bool True if a custom template was used, False if fallback logic should be used.
     */
    public function sendNotification(string|CommunicationTemplate $trigger, User|string $recipient, array $data = []): bool
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

        // Determine Locale and Restaurant
        $restaurant = null;
        $recipientEmail = $recipient instanceof User ? $recipient->email : $recipient;
        // Try to find user if string passed, to get context? No, just rely on passed data or existing logic.

        if ($trigger instanceof CommunicationTemplate && $trigger->restaurant_id) {
            $restaurant = $trigger->restaurant;
        } elseif ($recipient instanceof User) {
            $restaurant = $recipient->currentRestaurant();
        } else {
            // If recipient is string, we might need restaurant passed in $data?
            // Or if we are sending to restaurant notification_email, we likely know the restaurant context elsewhere.
            // But let's assume system template for now if no restaurant found.
            if (isset($data['restaurant_id'])) {
                $restaurant = \App\Models\Restaurant::find($data['restaurant_id']);
            }
        }

        // For System Templates (where restaurant_id is null), we enforce Bilingual Output
        // For Custom Restaurant Templates, we respect the restaurant's locale.
        $isSystemTemplate = is_null($template->restaurant_id);

        if ($isSystemTemplate) {
            // BILINGUAL MODE
            $subjectEn = $template->subject_en ?? $template->subject;
            $subjectAr = $template->subject_ar;

            // Combine Subject: "English / Arabic"
            $subject = $subjectEn;
            if ($subjectAr && $subjectAr !== $subjectEn) {
                $subject .= ' / ' . $subjectAr;
            }

            // Combine Content: English <hr> Arabic
            $contentEn = $template->content_en ?? $template->content;
            $contentAr = $template->content_ar;

            $content = $contentEn;
            if ($contentAr && $contentAr !== $contentEn) {
                $content .= "<hr style='border: 0; border-top: 1px solid #e5e7eb; margin: 32px 0;'>" . $contentAr;
            }

            // Do replacements on the combined string
            $subject = $this->replaceVariables($subject, $recipient, $data);
            $content = $this->replaceVariables($content, $recipient, $data);

        } else {
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

            $subject = $this->replaceVariables($subjectText, $recipient, $data);
            $content = $this->replaceVariables($contentText, $recipient, $data);
        }

        // Send Generic Email
        try {
            Mail::to($recipientEmail)->send(new \App\Mail\GenericSystemEmail($subject, $content));

            // Log the communication
            \App\Models\CommunicationLog::create([
                'restaurant_id' => $restaurant ? $restaurant->id : null,
                'communication_template_id' => $template->id,
                'recipient' => $recipientEmail,
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
                'recipient' => $recipientEmail,
                'type' => 'email',
                'status' => 'failed',
                'message' => substr($content, 0, 1000),
                'error_message' => $e->getMessage(),
                'sent_at' => now(),
            ]);

            return false;
        }
    }

    protected function replaceVariables(string $text, User|string $recipient, array $data): string
    {
        $name = ($recipient instanceof User) ? $recipient->name : ($data['name'] ?? 'Partner');
        $email = ($recipient instanceof User) ? $recipient->email : $recipient;

        $vars = [
            '{{ name }}' => $name,
            '{{ email }}' => $email,
            '{{ link }}' => $data['link'] ?? '#',
            '{{ restaurant_name }}' => $data['restaurant_name'] ?? config('app.name'),
            '{{ owner_email }}' => $data['owner_email'] ?? $email,
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
