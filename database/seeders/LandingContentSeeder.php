<?php

namespace Database\Seeders;

use App\Models\LandingModule;
use App\Models\LandingSetting;
use Illuminate\Database\Seeder;

class LandingContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Settings
        LandingSetting::set('contact_email', 'admin@demo.com');
        LandingSetting::set('about_us_title', [
            'en' => 'Empowering Restaurants Everywhere',
            'ar' => 'تمكين المطاعم في كل مكان'
        ]);
        LandingSetting::set('about_us_description', [
            'en' => "Servio is an all-in-one restaurant management platform designed to streamline operations, boost efficiency, and enhance customer experience.\n\nFrom a powerful Point of Sale (POS) system to advanced Kitchen Display Systems (KDS), Inventory Management, and Customer Loyalty programs, we provide everything you need to run a successful restaurant business.",
            'ar' => "Servio هي منصة شاملة لإدارة المطاعم مصممة لتبسيط العمليات وزيادة الكفاءة وتعزيز تجربة العملاء.\n\nمن نظام نقاط البيع القوي (POS) إلى أنظمة عرض المطبخ المتقدمة (KDS)، وإدارة المخزون، وبرامج ولاء العملاء، نوفر كل ما تحتاجه لإدارة مشروع مطعم ناجح."
        ]);
        // Default Stats
        LandingSetting::set('stats_restaurants', '500+');
        LandingSetting::set('stats_orders', '1M+');
        LandingSetting::set('stats_uptime', '99.9%');
        LandingSetting::set('stats_visible', true);


        // Clear existing modules to avoid duplicates and ensure clean state
        LandingModule::truncate();

        // Modules
        $modules = [
            [
                'icon' => '🖥️',
                'title' => ['en' => 'Point of Sale (POS)', 'ar' => 'نقطة البيع'],
                'description' => [
                    'en' => 'Fast, intuitive, and reliable POS interface designed for high-volume environments.',
                    'ar' => 'واجهة نقطة بيع سريعة وسهلة الاستخدام وموثوقة مصممة للبيئات عالية الدوران.'
                ],
                'sort_order' => 1,
                'is_active' => true
            ],
            [
                'icon' => '🍳',
                'title' => ['en' => 'Kitchen Display System', 'ar' => 'نظام عرض المطبخ'],
                'description' => [
                    'en' => 'Streamline communication between front-of-house and kitchen with real-time order updates.',
                    'ar' => 'تبسيط التواصل بين الصالة والمطبخ مع تحديثات الطلبات في الوقت الفعلي.'
                ],
                'sort_order' => 2,
                'is_active' => true
            ],
            [
                'icon' => '📦',
                'title' => ['en' => 'Inventory Management', 'ar' => 'إدارة المخزون'],
                'description' => [
                    'en' => 'Track stock levels, manage recipes, and reduce waste with precision inventory tracking.',
                    'ar' => 'تتبع مستويات المخزون، وإدارة الوصفات، وتقليل الهدر من خلال تتبع المخزون بدقة.'
                ],
                'sort_order' => 3,
                'is_active' => true
            ],
            [
                'icon' => '🎁',
                'title' => ['en' => 'Loyalty Program', 'ar' => 'برنامج الولاء'],
                'description' => [
                    'en' => 'Retain customers with a built-in points and rewards system tailored to your brand.',
                    'ar' => 'احتفظ بالعملاء مع نظام النقاط والمكافآت المدمج المصمم خصيصاً لعلامتك التجارية.'
                ],
                'sort_order' => 4,
                'is_active' => true
            ],
            [
                'icon' => '📊',
                'title' => ['en' => 'Analytics & Reports', 'ar' => 'التحليلات والتقارير'],
                'description' => [
                    'en' => 'Gain actionable insights with comprehensive sales, profit, and performance reports.',
                    'ar' => 'احصل على رؤى قابلة للتنفيذ من خلال تقارير شاملة للمبيعات والأرباح والأداء.'
                ],
                'sort_order' => 5,
                'is_active' => true
            ],
            [
                'icon' => '🛵',
                'title' => ['en' => 'Delivery Integration', 'ar' => 'تكامل التوصيل'],
                'description' => [
                    'en' => 'Seamlessly receive orders from major food delivery aggregators directly to your POS.',
                    'ar' => 'استقبل الطلبات من تطبيقات توصيل الطعام الرئيسية مباشرةً إلى نقطة البيع الخاصة بك.'
                ],
                'sort_order' => 6,
                'is_active' => true
            ],
            [
                'icon' => '📱',
                'title' => ['en' => 'QR Ordering', 'ar' => 'الطلب عبر الباركود'],
                'description' => [
                    'en' => 'Allow customers to order and pay directly from their table using QR codes.',
                    'ar' => 'اسمح للعملاء بالطلب والدفع مباشرة من طاولاتهم باستخدام رموز الباركود.'
                ],
                'sort_order' => 7,
                'is_active' => true
            ],
            [
                'icon' => '📢',
                'title' => ['en' => 'Marketing & Communication', 'ar' => 'التسويق والتواصل'],
                'description' => [
                    'en' => 'Engage with customers via SMS and Email campaigns to boost retention.',
                    'ar' => 'تفاعل مع العملاء عبر حملات الرسائل النصية والبريد الإلكتروني لزيادة ولاء العملاء.'
                ],
                'sort_order' => 8,
                'is_active' => true
            ],
            [
                'icon' => '⭐',
                'title' => ['en' => 'Customer Feedback', 'ar' => 'آراء العملاء'],
                'description' => [
                    'en' => 'Collect valuable feedback to improve your service and customer satisfaction.',
                    'ar' => 'اجمع آراءً قيمة لتحسين خدمتك ورضا العملاء.'
                ],
                'sort_order' => 9,
                'is_active' => true
            ],
        ];

        foreach ($modules as $module) {
            LandingModule::create($module);
        }
    }
}
