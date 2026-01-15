<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CommunicationTemplate;

class SystemEmailTemplatesSeeder extends Seeder
{
    public function run()
    {
        // Define Footers
        $footerEn = "
            <div style='margin-top: 32px; padding-top: 24px; border-top: 1px solid #e5e7eb; text-align: center; color: #6b7280; font-size: 14px;'>
                <p style='margin-bottom: 8px;'>Need Help? Contact Support</p>
                <p style='font-size: 16px; font-weight: 700; margin-bottom: 0;'>
                    <a href='tel:+971504946097' style='color: #4F46E5; text-decoration: none;'>+971 50 494 6097</a>
                </p>
            </div>";

        $footerAr = "
            <div dir='rtl' style='margin-top: 32px; padding-top: 24px; border-top: 1px solid #e5e7eb; text-align: center; color: #6b7280; font-size: 14px;'>
                <p style='margin-bottom: 8px;'>هل تحتاج مساعدة؟ اتصل بالدعم</p>
                <p style='font-size: 16px; font-weight: 700; margin-bottom: 0;'>
                    <a href='tel:+971504946097' style='color: #4F46E5; text-decoration: none;'>+971 50 494 6097</a>
                </p>
            </div>";

        $templates = [
            // 1. User Registration
            [
                'name' => 'User Registration Welcome',
                'trigger_event' => 'user_registered',
                'channels' => ['email'],
                'subject_en' => 'Welcome to Servio!',
                'subject_ar' => 'مرحباً بك في سيرفيو',
                'content_en' => "
                    <div dir='ltr' style='text-align: left;'>
                        <h1 style='color: #111827; font-size: 24px; font-weight: 700; margin-bottom: 24px;'>Welcome to Servio!</h1>
                        <p style='margin-bottom: 16px; font-size: 16px;'>Hello <b>{{ name }}</b>,</p>
                        <p style='margin-bottom: 16px; font-size: 16px;'>Thank you for joining Servio. We differenciate ourselves by providing the best all-in-one centralized restaurant management solution.</p>
                        <p style='margin-bottom: 24px; font-size: 16px;'>If you did not create this account, please ignore this email.</p>
                        <p style='font-size: 16px;'>Best Regards,<br>The Servio Team</p>
                    </div>",
                'content_ar' => "
                    <div dir='rtl' style='text-align: right;'>
                        <h1 style='color: #111827; font-size: 24px; font-weight: 700; margin-bottom: 24px;'>مرحباً بك في سيرفيو!</h1>
                        <p style='margin-bottom: 16px; font-size: 16px;'>مرحباً <b>{{ name }}</b>،</p>
                        <p style='margin-bottom: 16px; font-size: 16px;'>شكراً لانضمامك إلى سيرفيو. نحن نتميز بتقديم أفضل حل شامل لإدارة المطاعم بشكل مركزي.</p>
                        <p style='margin-bottom: 24px; font-size: 16px;'>إذا لم تقم بإنشاء هذا الحساب، يرجى تجاهل هذا البريد الإلكتروني.</p>
                        <p style='font-size: 16px;'>مع أطيب التحيات،<br>فريق سيرفيو</p>
                    </div>",
                'is_active' => true,
                'timing_type' => 'immediately'
            ],

            // 2. Restaurant Created (Welcome & Set Password)
            [
                'name' => 'Restaurant Created Successfully',
                'trigger_event' => 'restaurant_created',
                'channels' => ['email'],
                'subject_en' => 'Welcome to Servio - Account Setup',
                'subject_ar' => 'إعداد الحساب - سيرفيو',
                'content_en' => "
                    <div dir='ltr' style='text-align: left;'>
                        <h1 style='color: #111827; font-size: 24px; font-weight: 700; margin-bottom: 24px;'>Account Setup Complete</h1>
                        <p style='margin-bottom: 16px; font-size: 16px;'>Congratulations! Your restaurant <b>{{ restaurant_name }}</b> has been successfully set up on Servio.</p>
                        <p style='margin-bottom: 24px; font-size: 16px;'>To insure the security of your account, please set your password by clicking the button below:</p>
                        <div style='text-align: center; margin-bottom: 32px;'>
                            <a href='{{ link }}' style='background-color: #4F46E5; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-block;'>Set My Password</a>
                        </div>
                        <p style='font-size: 14px; color: #6B7280;'>This link is valid for 60 minutes.</p>
                    </div>",
                'content_ar' => "
                    <div dir='rtl' style='text-align: right;'>
                        <h1 style='color: #111827; font-size: 24px; font-weight: 700; margin-bottom: 24px;'>اكتمل إعداد الحساب</h1>
                        <p style='margin-bottom: 16px; font-size: 16px;'>مبروك! تم إعداد مطعمك <b>{{ restaurant_name }}</b> بنجاح على منصة سيرفيو.</p>
                        <p style='margin-bottom: 24px; font-size: 16px;'>لضمان أمان حسابك، يرجى تعيين كلمة المرور الخاصة بك عن طريق النقر على الزر أدناه:</p>
                        <div style='text-align: center; margin-bottom: 32px;'>
                            <a href='{{ link }}' style='background-color: #4F46E5; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-block;'>تعيين كلمة المرور</a>
                        </div>
                        <p style='font-size: 14px; color: #6B7280;'>هذا الرابط صالح لمدة 60 دقيقة.</p>
                    </div>",
                'is_active' => true,
                'timing_type' => 'immediately'
            ],

            // 3. Password Reset
            [
                'name' => 'Password Reset',
                'trigger_event' => 'password_reset',
                'channels' => ['email'],
                'subject_en' => 'Reset Your Password - Servio',
                'subject_ar' => 'استعادة كلمة المرور',
                'content_en' => "
                    <div dir='ltr' style='text-align: left;'>
                        <h1 style='color: #111827; font-size: 24px; font-weight: 700; margin-bottom: 24px;'>Password Reset Request</h1>
                        <p style='margin-bottom: 16px; font-size: 16px;'>Hello {{ name }},</p>
                        <p style='margin-bottom: 24px; font-size: 16px;'>We received a request to reset your password. No changes have been made to your account yet.</p>
                        <div style='text-align: center; margin-bottom: 32px;'>
                            <a href='{{ link }}' style='background-color: #4F46E5; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-block;'>Reset Password</a>
                        </div>
                        <p style='font-size: 16px;'>If you did not request this, please ignore this email.</p>
                    </div>",
                'content_ar' => "
                    <div dir='rtl' style='text-align: right;'>
                        <h1 style='color: #111827; font-size: 24px; font-weight: 700; margin-bottom: 24px;'>طلب استعادة كلمة المرور</h1>
                        <p style='margin-bottom: 16px; font-size: 16px;'>مرحباً {{ name }}،</p>
                        <p style='margin-bottom: 24px; font-size: 16px;'>لقد تلقينا طلباً لاستعادة كلمة المرور الخاصة بك. لم يتم إجراء أي تغييرات على حسابك بعد.</p>
                        <div style='text-align: center; margin-bottom: 32px;'>
                            <a href='{{ link }}' style='background-color: #4F46E5; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-block;'>استعادة كلمة المرور</a>
                        </div>
                        <p style='font-size: 16px;'>إذا لم تقم بطلب هذا، يرجى تجاهل هذا البريد الإلكتروني.</p>
                    </div>",
                'is_active' => true,
                'timing_type' => 'immediately'
            ],

            // 4. Subscription Confirmed
            [
                'name' => 'Subscription Confirmation',
                'trigger_event' => 'subscription_created',
                'channels' => ['email'],
                'subject_en' => 'Subscription Confirmed',
                'subject_ar' => 'تأكيد الاشتراك',
                'content_en' => "
                    <div dir='ltr' style='text-align: left;'>
                        <h1 style='color: #111827; font-size: 24px; font-weight: 700; margin-bottom: 24px;'>Subscription Active</h1>
                        <p style='margin-bottom: 16px; font-size: 16px;'>Hello {{ name }},</p>
                        <p style='margin-bottom: 16px; font-size: 16px;'>Your subscription to the <b>{{ plan_name }}</b> plan for <b>{{ restaurant_name }}</b> is now active.</p>
                        <p style='margin-bottom: 16px; font-size: 16px;'>Expiry Date: <b>{{ expiry_date }}</b></p>
                        <p style='font-size: 16px;'>Thank you for choosing Servio to power your business.</p>
                    </div>",
                'content_ar' => "
                    <div dir='rtl' style='text-align: right;'>
                        <h1 style='color: #111827; font-size: 24px; font-weight: 700; margin-bottom: 24px;'>الاشتراك مفعل</h1>
                        <p style='margin-bottom: 16px; font-size: 16px;'>مرحباً {{ name }}،</p>
                        <p style='margin-bottom: 16px; font-size: 16px;'>تم تفعيل اشتراكك في باقة <b>{{ plan_name }}</b> لمطعم <b>{{ restaurant_name }}</b>.</p>
                        <p style='margin-bottom: 16px; font-size: 16px;'>تاريخ الانتهاء: <b>{{ expiry_date }}</b></p>
                        <p style='font-size: 16px;'>شكراً لاختيارك سيرفيو لإدارة أعمالك.</p>
                    </div>",
                'is_active' => true,
                'timing_type' => 'immediately'
            ],

            // 5. Subscription Warning
            [
                'name' => 'Subscription Expiry Warning',
                'trigger_event' => 'subscription_warning',
                'channels' => ['email'],
                'subject_en' => 'Action Required: Subscription Expiring Soon',
                'subject_ar' => 'إجراء مطلوب: قُرب انتهاء مدة الاشتراك',
                'content_en' => "
                    <div dir='ltr' style='text-align: left;'>
                        <h1 style='color: #B91C1C; font-size: 24px; font-weight: 700; margin-bottom: 24px;'>Subscription Expiring Soon</h1>
                        <p style='margin-bottom: 16px; font-size: 16px;'>Hello {{ name }},</p>
                        <p style='margin-bottom: 16px; font-size: 16px;'>This is a reminder that your subscription for <b>{{ restaurant_name }}</b> will expire in <b>3 days</b> ({{ expiry_date }}).</p>
                        <p style='margin-bottom: 24px; font-size: 16px;'>To avoid any interruption in your service, please renew your plan now.</p>
                        <div style='text-align: center;'>
                            <a href='{{ link }}' style='background-color: #B91C1C; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-block;'>Renew Subscription</a>
                        </div>
                    </div>",
                'content_ar' => "
                    <div dir='rtl' style='text-align: right;'>
                        <h1 style='color: #B91C1C; font-size: 24px; font-weight: 700; margin-bottom: 24px;'>الاشتراك ينتهي قريباً</h1>
                        <p style='margin-bottom: 16px; font-size: 16px;'>مرحباً {{ name }}،</p>
                        <p style='margin-bottom: 16px; font-size: 16px;'>هذا تذكير بأن اشتراكك لمطعم <b>{{ restaurant_name }}</b> سينتهي خلال <b>3 أيام</b> ({{ expiry_date }}).</p>
                        <p style='margin-bottom: 24px; font-size: 16px;'>لتجنب أي انقطاع في الخدمة، يرجى تجديد اشتراكك الآن.</p>
                        <div style='text-align: center;'>
                            <a href='{{ link }}' style='background-color: #B91C1C; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-block;'>تجديد الاشتراك</a>
                        </div>
                    </div>",
                'is_active' => true,
                'timing_type' => 'before',
                'timing_days' => 3
            ],

            // 6. Subscription Expired
            [
                'name' => 'Subscription Expired',
                'trigger_event' => 'subscription_expired',
                'channels' => ['email'],
                'subject_en' => 'Service Interruption: Subscription Expired',
                'subject_ar' => 'انقطاع الخدمة: انتهاء صلاحية الاشتراك',
                'content_en' => "
                    <div dir='ltr' style='text-align: left;'>
                        <h1 style='color: #111827; font-size: 24px; font-weight: 700; margin-bottom: 24px;'>Subscription Expired</h1>
                        <p style='margin-bottom: 16px; font-size: 16px;'>Hello {{ name }},</p>
                        <p style='margin-bottom: 16px; font-size: 16px;'>Your subscription for <b>{{ restaurant_name }}</b> expired on <b>{{ expiry_date }}</b>.</p>
                        <p style='margin-bottom: 24px; font-size: 16px;'>Access to premium features has been restricted. Please renew immediately to restore full access.</p>
                        <div style='text-align: center;'>
                            <a href='{{ link }}' style='background-color: #4F46E5; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-block;'>Restore Access</a>
                        </div>
                    </div>",
                'content_ar' => "
                    <div dir='rtl' style='text-align: right;'>
                        <h1 style='color: #111827; font-size: 24px; font-weight: 700; margin-bottom: 24px;'>انتهت صلاحية الاشتراك</h1>
                        <p style='margin-bottom: 16px; font-size: 16px;'>مرحباً {{ name }}،</p>
                        <p style='margin-bottom: 16px; font-size: 16px;'>لقد انتهت صلاحية اشتراكك لمطعم <b>{{ restaurant_name }}</b> بتاريخ <b>{{ expiry_date }}</b>.</p>
                        <p style='margin-bottom: 24px; font-size: 16px;'>تم تقييد الوصول إلى الميزات المدفوعة. يرجى التجديد فوراً لاستعادة الوصول الكامل.</p>
                        <div style='text-align: center;'>
                            <a href='{{ link }}' style='background-color: #4F46E5; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-block;'>استعادة الوصول</a>
                        </div>
                    </div>",
                'is_active' => true,
                'timing_type' => 'immediately'
            ],

            // 7. Inventory Expiry Warning
            [
                'name' => 'Inventory Expiry Warning',
                'trigger_event' => 'inventory_expiry_warning',
                'channels' => ['email'],
                'subject_en' => 'Inventory Alert: Batch Expiring Soon',
                'subject_ar' => 'تنبيه المخزون: دفعة قاربت على الانتهاء',
                'content_en' => "
                    <div dir='ltr' style='text-align: left;'>
                        <h1 style='color: #F59E0B; font-size: 24px; font-weight: 700; margin-bottom: 24px;'>Inventory Expiry Warning</h1>
                        <p style='margin-bottom: 16px; font-size: 16px;'>The following batch is expiring in <b>{{ days_remaining }} days</b>:</p>
                        <ul style='background-color: #F3F4F6; padding: 16px; border-radius: 8px; list-style: none; margin-bottom: 24px;'>
                            <li style='margin-bottom: 8px;'><b>Item:</b> {{ ingredient_name_en }}</li>
                            <li style='margin-bottom: 8px;'><b>Batch:</b> {{ batch_number }}</li>
                            <li style='margin-bottom: 8px;'><b>Qty Remaining:</b> {{ quantity_remaining }}</li>
                            <li><b>Expiry Date:</b> {{ expiry_date }}</li>
                        </ul>
                        <p style='font-size: 16px;'>Please use or discard this stock promptly to maintain quality standards.</p>
                    </div>",
                'content_ar' => "
                    <div dir='rtl' style='text-align: right;'>
                        <h1 style='color: #F59E0B; font-size: 24px; font-weight: 700; margin-bottom: 24px;'>تحذير انتهاء صلاحية المخزون</h1>
                        <p style='margin-bottom: 16px; font-size: 16px;'>الدفعة التالية ستنتهي صلاحيتها خلال <b>{{ days_remaining }} أيام</b>:</p>
                        <ul style='background-color: #F3F4F6; padding: 16px; border-radius: 8px; list-style: none; margin-bottom: 24px;'>
                            <li style='margin-bottom: 8px;'><b>العنصر:</b> {{ ingredient_name_ar }}</li>
                            <li style='margin-bottom: 8px;'><b>رقم الدفعة:</b> {{ batch_number }}</li>
                            <li style='margin-bottom: 8px;'><b>الكمية المتبقية:</b> {{ quantity_remaining }}</li>
                            <li><b>تاريخ الانتهاء:</b> {{ expiry_date }}</li>
                        </ul>
                        <p style='font-size: 16px;'>يرجى استخدام هذا المخزون أو التخلص منه فوراً للحفاظ على معايير الجودة.</p>
                    </div>",
                'is_active' => true,
                'timing_type' => 'immediately'
            ],

            // 8. Low Stock Warning
            [
                'name' => 'Inventory Low Stock Warning',
                'trigger_event' => 'inventory_low_stock_warning',
                'channels' => ['email'],
                'subject_en' => 'Low Stock Alert: {{ ingredient_name_en }}',
                'subject_ar' => 'تنبيه انخفاض المخزون: {{ ingredient_name_ar }}',
                'content_en' => "
                    <div dir='ltr' style='text-align: left;'>
                        <h1 style='color: #B91C1C; font-size: 24px; font-weight: 700; margin-bottom: 24px;'>Low Stock Alert</h1>
                        <p style='margin-bottom: 16px; font-size: 16px;'><b>{{ ingredient_name_en }}</b> has reached the reorder level.</p>
                        <ul style='background-color: #FEF2F2; padding: 16px; border-radius: 8px; list-style: none; margin-bottom: 24px;'>
                            <li style='margin-bottom: 8px;'><b>Current Stock:</b> {{ current_stock }}</li>
                            <li><b>Reorder Level:</b> {{ reorder_level }}</li>
                        </ul>
                        <p style='font-size: 16px;'>Please restock this item soon to avoid running out.</p>
                    </div>",
                'content_ar' => "
                    <div dir='rtl' style='text-align: right;'>
                        <h1 style='color: #B91C1C; font-size: 24px; font-weight: 700; margin-bottom: 24px;'>تنبيه انخفاض المخزون</h1>
                        <p style='margin-bottom: 16px; font-size: 16px;'>لقد وصل <b>{{ ingredient_name_ar }}</b> إلى حد إعادة الطلب.</p>
                        <ul style='background-color: #FEF2F2; padding: 16px; border-radius: 8px; list-style: none; margin-bottom: 24px;'>
                            <li style='margin-bottom: 8px;'><b>المخزون الحالي:</b> {{ current_stock }}</li>
                            <li><b>حد إعادة الطلب:</b> {{ reorder_level }}</li>
                        </ul>
                        <p style='font-size: 16px;'>يرجى إعادة تزويد هذا الصنف قريباً لتجنب نفاذ الكمية.</p>
                    </div>",
                'is_active' => true,
                'timing_type' => 'immediately'
            ]
        ];

        foreach ($templates as $data) {
            CommunicationTemplate::updateOrCreate(
                [
                    'trigger_event' => $data['trigger_event'],
                    'restaurant_id' => null, // Ensure system template
                ],
                [
                    'name' => $data['name'],
                    'channels' => $data['channels'],
                    'subject_en' => $data['subject_en'],
                    'subject_ar' => $data['subject_ar'],
                    'content_en' => $data['content_en'] . $footerEn, // CLEAN ENGLISH content
                    'content_ar' => $data['content_ar'] . $footerAr, // CLEAN ARABIC content
                    'is_active' => $data['is_active'],
                    'timing_type' => $data['timing_type'],
                    'timing_days' => $data['timing_days'] ?? 0,
                    'conditions' => []
                ]
            );
        }
    }
}
