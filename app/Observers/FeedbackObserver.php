<?php

namespace App\Observers;

class FeedbackObserver
{
    public function created(\App\Models\Feedback $feedback): void
    {
        // Find matching automation rules
        $topRule = \App\Models\CommunicationTemplate::where('restaurant_id', $feedback->restaurant_id)
            ->where('is_active', true)
            ->where('trigger_event', 'feedback_received')
            ->get()
            ->filter(function ($template) use ($feedback) {
                $conditions = $template->conditions ?? [];

                // Check Rating
                if (isset($conditions['min_rating']) && $feedback->rating < $conditions['min_rating']) {
                    return false;
                }
                if (isset($conditions['max_rating']) && $feedback->rating > $conditions['max_rating']) {
                    return false;
                }

                return true;
            })
            ->first(); // For simplicity, take the first matching rule, or loop them all

        if ($topRule) {
            // Dispatch job to send message
            // Ideally: dispatch(new SendCommunicationJob($topRule, $feedback->customer));
            // For now, let's assume direct sending or similar logic exist
            // In a real app, we'd queue this.
            if ($feedback->customer) {
                // Placeholder for sending logic
                // \App\Services\CommunicationService::send($topRule, $feedback->customer, ['feedback_link' => ...]);
            }
        }
    }
}
