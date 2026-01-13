<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CommunicationTemplate;
use App\Models\Restaurant;

class CommunicationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $restaurants = Restaurant::all();

        foreach ($restaurants as $restaurant) {
            $this->seedTemplatesForRestaurant($restaurant);
        }

        // Also seed system templates (restaurant_id = null)
        $this->seedSystemTemplates();
    }

    private function seedTemplatesForRestaurant($restaurant)
    {
        $templates = [
            [
                'trigger_event' => 'order_completed',
                'name' => 'Order Completion Thank You',
                'channels' => ['email', 'sms'],
                'subject_en' => '',
                'subject_ar' => '',
                'content_en' => '',
                'content_ar' => '',
                'sms_content_en' => '',
                'sms_content_ar' => '',
                'is_active' => true,
                'timing_type' => 'immediately',
            ],
            [
                'trigger_event' => 'order_completed_feedback',
                'name' => 'Post-Order Feedback Request',
                'channels' => ['email', 'sms'],
                'subject_en' => '',
                'subject_ar' => '',
                'content_en' => '',
                'content_ar' => '',
                'sms_content_en' => '',
                'sms_content_ar' => '',
                'is_active' => true,
                'timing_type' => 'after',
                'timing_days' => 0,
                'timing_time' => '01:00',
            ],
            [
                'trigger_event' => 'feedback_received',
                'name' => 'Feedback Thank You',
                'channels' => ['email'],
                'subject_en' => '',
                'subject_ar' => '',
                'content_en' => '',
                'content_ar' => '',
                'is_active' => true,
                'timing_type' => 'immediately',
            ],
            [
                'trigger_event' => 'inventory_low_stock_warning',
                'name' => 'Low Stock Alert',
                'channels' => ['email'],
                'subject_en' => '',
                'subject_ar' => '',
                'content_en' => '',
                'content_ar' => '',
                'is_active' => true,
                'timing_type' => 'immediately',
            ],
            [
                'trigger_event' => 'inventory_expiry_warning',
                'name' => 'Item Expiry Alert',
                'channels' => ['email'],
                'subject_en' => '',
                'subject_ar' => '',
                'content_en' => '',
                'content_ar' => '',
                'is_active' => true,
                'timing_type' => 'immediately',
            ],
            [
                'trigger_event' => 'loyalty_points_earned',
                'name' => 'Loyalty Points Earned',
                'channels' => ['email', 'sms'],
                'subject_en' => '',
                'subject_ar' => '',
                'content_en' => '',
                'content_ar' => '',
                'sms_content_en' => '',
                'sms_content_ar' => '',
                'is_active' => true,
                'timing_type' => 'immediately',
            ],
            [
                'trigger_event' => 'loyalty_tier_upgraded',
                'name' => 'Loyalty Tier Upgraded',
                'channels' => ['email', 'sms'],
                'subject_en' => '',
                'subject_ar' => '',
                'content_en' => '',
                'content_ar' => '',
                'sms_content_en' => '',
                'sms_content_ar' => '',
                'is_active' => true,
                'timing_type' => 'immediately',
            ],
        ];

        foreach ($templates as $template) {
            CommunicationTemplate::updateOrCreate(
                [
                    'restaurant_id' => $restaurant->id,
                    'trigger_event' => $template['trigger_event']
                ],
                $template
            );
        }
    }

    private function seedSystemTemplates()
    {
        $systemTemplates = [
            [
                'trigger_event' => 'user_registered',
                'name' => 'User Welcome / Invitation',
                'channels' => ['email'],
                'subject_en' => '',
                'subject_ar' => '',
                'content_en' => '',
                'content_ar' => '',
                'is_active' => true,
                'timing_type' => 'immediately',
            ],
            [
                'trigger_event' => 'password_reset',
                'name' => 'Password Reset Request',
                'channels' => ['email'],
                'subject_en' => '',
                'subject_ar' => '',
                'content_en' => '',
                'content_ar' => '',
                'is_active' => true,
                'timing_type' => 'immediately',
            ],
            [
                'trigger_event' => 'subscription_warning',
                'name' => 'Subscription Renewal Reminder',
                'channels' => ['email'],
                'subject_en' => '',
                'subject_ar' => '',
                'content_en' => '',
                'content_ar' => '',
                'is_active' => true,
                'timing_type' => 'before',
                'timing_days' => 7,
                'timing_time' => '09:00',
            ],
            [
                'trigger_event' => 'subscription_expired',
                'name' => 'Subscription Expired Alert',
                'channels' => ['email'],
                'subject_en' => '',
                'subject_ar' => '',
                'content_en' => '',
                'content_ar' => '',
                'is_active' => true,
                'timing_type' => 'immediately',
            ],
        ];

        foreach ($systemTemplates as $template) {
            CommunicationTemplate::updateOrCreate(
                [
                    'restaurant_id' => null,
                    'trigger_event' => $template['trigger_event']
                ],
                $template
            );
        }
    }
}
