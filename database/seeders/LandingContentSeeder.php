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
            'en' => 'Elevating Every Aspect of Your Restaurant',
            'ar' => 'الارتقاء بكل جانب من جوانب مطعمك'
        ]);
        LandingSetting::set('about_us_description', [
            'en' => "Servio is a state-of-the-art restaurant management ecosystem designed to empower modern food businesses. Our platform goes beyond simple transactions, offering a seamless integration of Point of Sale (POS), Kitchen Display Systems (KDS), and Multi-Channel Delivery Management.\n\nWe specialize in enhancing customer engagement through our advanced Loyalty Program, integrated Feedback Forms, and customizable Receipt Templates. With smart Inventory Management and automated reminders, we help you reduce waste and focus on what matters most: delivering exceptional dining experiences.",
            'ar' => "سيرفيو هو نظام بيئي متكامل لإدارة المطاعم مصمم لتمكين الشركات الغذائية الحديثة. تتجاوز منصتنا مجرد المعاملات البسيطة، حيث تقدم تكاملاً سلساً لنقاط البيع (POS)، وأنظمة عرض المطبخ (KDS)، وإدارة التوصيل عبر قنوات متعددة.\n\nنحن متخصصون في تعزيز مشاركة العملاء من خلال برنامج الولاء المتقدم، ونماذج الملاحظات المتكاملة، وقوالب الإيصالات القابلة للتخصيص. بفضل إدارة المخزون الذكية والتذكيرات الآلية، نساعدك على تقليل الهدر والتركيز على الأهم: تقديم تجارب طعام استثنائية."
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
                    'en' => 'Fast, intuitive POS with **fully customizable receipts** designed for high-volume environments.',
                    'ar' => 'واجهة نقطة بيع سريعة وسهلة مع **إيصالات قابلة للتخصيص بالكامل** مصممة للبيئات عالية الدوران.'
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
                    'en' => 'Track stock, manage recipes, and obtain **smart low-stock and expiry reminders** to reduce waste.',
                    'ar' => 'تتبع المخزون، وإدارة الوصفات، والحصول على **تذكيرات ذكية بانخفاض المخزون وانتهاء الصلاحية** لتقليل الهدر.'
                ],
                'sort_order' => 3,
                'is_active' => true
            ],
            [
                'icon' => '🎁',
                'title' => ['en' => 'Loyalty Program', 'ar' => 'برنامج الولاء'],
                'description' => [
                    'en' => 'Retain customers with a **customizable loyalty program** tailored to your brand.',
                    'ar' => 'احتفظ بالعملاء مع **برنامج ولاء قابل للتخصيص** مصمم خصيصاً لعلامتك التجارية.'
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
                    'en' => 'Collect valuable insights with **fully customizable feedback forms** to improve satisfaction.',
                    'ar' => 'اجمع رؤى قيمة باستخدام **نماذج ملاحظات قابلة للتخصيص بالكامل** لتحسين الرضا.'
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
