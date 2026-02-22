<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CommunicationTemplate;

class DefaultSmsTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Registration OTP (OTP Only)',
                'trigger_event' => 'registration_otp',
                'content_en' => 'Your Servio verification code is: {{ otp }}. This code will expire in 10 minutes.',
                'content_ar' => 'رمز التحقق الخاص بك في سيرفيو هو: {{ otp }}. تنتهي صلاحية هذا الرمز خلال 10 دقائق.',
            ],
            [
                'name' => 'Welcome SMS',
                'trigger_event' => 'user_registered',
                'content_en' => 'Welcome to Servio, {{ name }}! Your account has been successfully created.',
                'content_ar' => 'مرحباً بك في سيرفيو، {{ name }}! تم إنشاء حسابك بنجاح.',
            ],
            [
                'name' => 'Password Reset SMS',
                'trigger_event' => 'password_reset',
                'content_en' => 'Your Servio password reset code/link is: {{ link }}',
                'content_ar' => 'رابط/رمز إعادة تعيين كلمة المرور الخاصة بك في سيرفيو هو: {{ link }}',
            ],
            [
                'name' => 'Subscription Created',
                'trigger_event' => 'subscription_created',
                'content_en' => 'Thank you for subscribing to {{ plan_name }}! Your subscription is now active.',
                'content_ar' => 'شكراً لاشتراكك في باقة {{ plan_name }}! اشتراكك مفعل الآن.',
            ],
            [
                'name' => 'Subscription Warning (Expiry)',
                'trigger_event' => 'subscription_warning',
                'content_en' => 'Warning: Your Servio subscription will expire in {{ days_remaining }} days. Please renew to avoid service interruption.',
                'content_ar' => 'تحذير: سينتهي اشتراكك في سيرفيو خلال {{ days_remaining }} أيام. يرجى التجديد لتجنب انقطاع الخدمة.',
            ],
            [
                'name' => 'Subscription Expired',
                'trigger_event' => 'subscription_expired',
                'content_en' => 'Your Servio subscription has expired. Please log in to renew your plan.',
                'content_ar' => 'لقد انتهى اشتراكك في سيرفيو. يرجى تسجيل الدخول لتجديد باقتك.',
            ],
            [
                'name' => 'Restaurant Created',
                'trigger_event' => 'restaurant_created',
                'content_en' => 'Congratulations! Your restaurant {{ restaurant_name }} has been successfully setup on Servio.',
                'content_ar' => 'تهانينا! تم إعداد مطعمك {{ restaurant_name }} بنجاح على سيرفيو.',
            ],
            [
                'name' => 'Loyalty OTP (Redemption)',
                'trigger_event' => 'loyalty_otp',
                'content_en' => 'Your Servio loyalty redemption code is: {{ otp }}.',
                'content_ar' => 'رمز استرداد نقاط الولاء الخاص بك في سيرفيو هو: {{ otp }}.',
            ]
        ];

        foreach ($templates as $template) {
            CommunicationTemplate::updateOrCreate(
                [
                    'restaurant_id' => null, // System templates have null restaurant_id
                    'trigger_event' => $template['trigger_event'],
                    'channels' => '["sms"]' // Store as JSON array string or use actual array if model casts it. The controller looks for '["sms"]' or exact match.
                ],
                [
                    'name' => $template['name'],
                    'channels' => ['sms'], // Assuming the model casts 'channels' to array.
                    'subject_en' => null, // SMS usually doesn't need subject
                    'subject_ar' => null,
                    'content_en' => $template['content_en'],
                    'content_ar' => $template['content_ar'],
                    'is_active' => true,
                    'timing_type' => 'immediately',
                    'timing_days' => 0,
                    'timing_time' => null,
                    'conditions' => []
                ]
            );
        }
    }
}
