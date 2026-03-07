<?php

namespace App\Http\Controllers;

use App\Mail\RegistrationInterest;
use App\Models\DeliveryProvider;
use App\Models\LandingModule;
use App\Models\LandingSetting;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class LandingPageController extends Controller
{
    public function index()
    {
        // Increment landing page visits
        $currentVisits = LandingSetting::get('landing_page_visits', ['count' => 0]);
        $newCount = ($currentVisits['count'] ?? 0) + 1;
        LandingSetting::set('landing_page_visits', ['count' => $newCount]);

        $plans = Plan::where('is_active', true)
            ->orderBy('order', 'asc')
            ->orderBy('price_monthly', 'asc')
            ->get();

        $locale = app()->getLocale();
        foreach ($plans as $plan) {
            if ($locale === 'ar') {
                $plan->name = $plan->name_ar ?: $plan->name;
                $plan->description = $plan->description_ar ?: $plan->description;
            } elseif ($locale === 'en') {
                $plan->name = $plan->name_en ?: $plan->name;
                $plan->description = $plan->description_en ?: $plan->description;
            }
        }

        $modules = LandingModule::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        $deliveryProviders = DeliveryProvider::active()
            ->ordered()
            ->select('id', 'name', 'logo_url')
            ->get();

        $dbSettings = LandingSetting::all()->mapWithKeys(function ($item) {
            return [$item->key => $item->value];
        });

        // Define defaults (Must match Admin/LandingPageController for consistency)
        $defaults = [
            'contact_email' => __('landing.connect_via_email'),
            'hero_title' => ['en' => __('landing.hero_title', [], 'en'), 'ar' => __('landing.hero_title', [], 'ar')],
            'hero_subtitle' => ['en' => __('landing.hero_subtitle', [], 'en'), 'ar' => __('landing.hero_subtitle', [], 'ar')],
            'about_us_title' => ['en' => __('landing.about_title_default', [], 'en'), 'ar' => __('landing.about_title_default', [], 'ar')],
            'about_us_description' => ['en' => __('landing.about_us_description_default', [], 'en'), 'ar' => __('landing.about_us_description_default', [], 'ar')],

            // Dashboard Defaults
            'dashboard_title' => ['en' => 'Comprehensive Restaurant Dashboard', 'ar' => 'لوحة تحكم شاملة للمطعم'],
            'dashboard_desc' => ['en' => 'Manage every aspect of your restaurant from a single, intuitive interface.', 'ar' => 'أدر كل جانب من جوانب مطعمك من واجهة واحدة سهلة الاستخدام.'],
            'dashboard_point_1' => ['en' => 'Real-time sales tracking and analytics', 'ar' => 'تتبع المبيعات والتحليلات في الوقت الفعلي'],
            'dashboard_point_2' => ['en' => 'Inventory and stock management', 'ar' => 'إدارة المخزون والمستودعات'],
            'dashboard_point_3' => ['en' => 'Staff performance monitoring', 'ar' => 'مراقبة أداء الموظفين'],
            'dashboard_point_4' => ['en' => 'Customer relationship management (CRM)', 'ar' => 'إدارة علاقات العملاء (CRM)'],

            'services_title' => ['en' => __('landing.our_services_title', [], 'en'), 'ar' => __('landing.our_services_title', [], 'ar')],
            'services_subtitle' => ['en' => 'Comprehensive solutions for your restaurant', 'ar' => 'حلول شاملة لمطعمك'],
            'software_services_title' => ['en' => __('landing.software_services_title', [], 'en'), 'ar' => __('landing.software_services_title', [], 'ar')],
            'software_services_desc' => ['en' => __('landing.software_services_desc', [], 'en'), 'ar' => __('landing.software_services_desc', [], 'ar')],
            'hardware_services_title' => ['en' => __('landing.hardware_services_title', [], 'en'), 'ar' => __('landing.hardware_services_title', [], 'ar')],
            'hardware_services_desc' => ['en' => __('landing.hardware_services_desc', [], 'en'), 'ar' => __('landing.hardware_services_desc', [], 'ar')],

            // Dashboard Widgets Details
            'dash_widget_1_title' => ['en' => '360° Financial Analytics', 'ar' => 'تحليلات مالية شاملة 360 درجة'],
            'dash_widget_1_desc' => [
                'en' => 'Real-time Profit & Loss tracking. Monitor Revenue, Monthly Expenses, and Net Profit instantly. Deep dive into "Item Sales" and "Payment Method" breakdowns to master your cash flow.',
                'ar' => 'تتبع الأرباح والخسائر في الوقت الفعلي. راقب الإيرادات والمصروفات الشهرية وصافي الربح فوراً. تعمق في تفاصيل "مبيعات الأصناف" و"طرق الدفع" لإتقان التدفق النقدي لديك.'
            ],

            'dash_widget_2_title' => ['en' => 'Operational Efficiency & Waste', 'ar' => 'الكفاءة التشغيلية والتحكم بالهدر'],
            'dash_widget_2_desc' => [
                'en' => 'Optimize staff scheduling with "Peak Hours" analysis. Track "Average Dining Time" for table turnover. Monitor "Waste Logs" daily to significantly reduce food costs and kitchen errors.',
                'ar' => 'حسن جداول الموظفين مع تحليل "ساعات الذروة". تتبع "متوسط وقت تناول الطعام" لزيادة دوران الطاولات. راقب "سجلات الهدر" يومياً لتقليل تكاليف الطعام وأخطاء المطبخ بشكل كبير.'
            ],

            'dash_widget_3_title' => ['en' => 'Advanced CRM & Retention', 'ar' => 'إدارة علاقات عملاء متقدمة'],
            'dash_widget_3_desc' => [
                'en' => 'Identify "Top Customers" by total spend. Track retention rates from 1st to 5th+ visit with our specialized funnel. Automate "Win-back" SMS campaigns for customers who haven\'t visited in 30 days.',
                'ar' => 'تعرف على "أهم العملاء" حسب إجمالي الإنفاق. تتبع معدلات الاحتفاظ من الزيارة الأولى حتى الخامسة فأكثر. أتمتة حملات الرسائل النصية لاستعادة العملاء الذين لم يزوروا المطعم منذ 30 يوماً.'
            ],

            'dash_widget_4_title' => ['en' => 'Smart Menu Intelligence', 'ar' => 'ذكاء القوائم المتقدم'],
            'dash_widget_4_desc' => [
                'en' => 'Rankings for "Top Categories" and "Best Sellers" by quantity and revenue. Filter stats by "Delivery Provider" (UberEats, Talabat) to see which high-margin items perform best on each platform.',
                'ar' => 'تصنيفات لـ "أعلى الفئات" و"الأكثر مبيعاً" حسب الكمية والإيرادات. صفي الإحصائيات حسب "مزود التوصيل" (UberEats, Talabat) لمعرفة العناصر ذات الهامش الربحي العالي الأفضل أداءً على كل منصة.'
            ],

            // Features Defaults
            'features_title' => ['en' => __('landing.features', [], 'en'), 'ar' => __('landing.features', [], 'ar')],
            'features_desc' => ['en' => __('landing.modules_description', [], 'en'), 'ar' => __('landing.modules_description', [], 'ar')],
            'feature_pos_title' => ['en' => __('landing.feature_pos_title', [], 'en'), 'ar' => __('landing.feature_pos_title', [], 'ar')],
            'feature_pos_desc' => ['en' => __('landing.feature_pos_desc', [], 'en'), 'ar' => __('landing.feature_pos_desc', [], 'ar')],
            'feature_kds_title' => ['en' => __('landing.feature_kds_title', [], 'en'), 'ar' => __('landing.feature_kds_title', [], 'ar')],
            'feature_kds_desc' => ['en' => __('landing.feature_kds_desc', [], 'en'), 'ar' => __('landing.feature_kds_desc', [], 'ar')],
            'feature_inventory_title' => ['en' => __('landing.feature_inventory_title', [], 'en'), 'ar' => __('landing.feature_inventory_title', [], 'ar')],
            'feature_inventory_desc' => ['en' => __('landing.feature_inventory_desc', [], 'en'), 'ar' => __('landing.feature_inventory_desc', [], 'ar')],
            'feature_loyalty_title' => ['en' => __('landing.feature_loyalty_title', [], 'en'), 'ar' => __('landing.feature_loyalty_title', [], 'ar')],
            'feature_loyalty_desc' => ['en' => __('landing.feature_loyalty_desc', [], 'en'), 'ar' => __('landing.feature_loyalty_desc', [], 'ar')],
            'inventory_bullet_1' => ['en' => __('landing.inventory_bullet_1', [], 'en'), 'ar' => __('landing.inventory_bullet_1', [], 'ar')],
            'inventory_bullet_2' => ['en' => __('landing.inventory_bullet_2', [], 'en'), 'ar' => __('landing.inventory_bullet_2', [], 'ar')],
            'loyalty_bullet_1' => ['en' => __('landing.loyalty_bullet_1', [], 'en'), 'ar' => __('landing.loyalty_bullet_1', [], 'ar')],
            'loyalty_bullet_2' => ['en' => __('landing.loyalty_bullet_2', [], 'en'), 'ar' => __('landing.loyalty_bullet_2', [], 'ar')],

            // How It Works Defaults
            'how_it_works_title' => ['en' => __('landing.how_it_works_title', [], 'en'), 'ar' => __('landing.how_it_works_title', [], 'ar')],
            'step_1_title' => ['en' => __('landing.step_1_title', [], 'en'), 'ar' => __('landing.step_1_title', [], 'ar')],
            'step_1_desc' => ['en' => __('landing.step_1_desc', [], 'en'), 'ar' => __('landing.step_1_desc', [], 'ar')],
            'step_2_title' => ['en' => __('landing.step_2_title', [], 'en'), 'ar' => __('landing.step_2_title', [], 'ar')],
            'step_2_desc' => ['en' => __('landing.step_2_desc', [], 'en'), 'ar' => __('landing.step_2_desc', [], 'ar')],
            'step_3_title' => ['en' => __('landing.step_3_title', [], 'en'), 'ar' => __('landing.step_3_title', [], 'ar')],
            'step_3_desc' => ['en' => __('landing.step_3_desc', [], 'en'), 'ar' => __('landing.step_3_desc', [], 'ar')],

            // Feedback Defaults
            'feedback_title' => ['en' => __('landing.feedback_title', [], 'en'), 'ar' => __('landing.feedback_title', [], 'ar')],
            'feedback_desc' => ['en' => __('landing.feedback_desc', [], 'en'), 'ar' => __('landing.feedback_desc', [], 'ar')],
            'feedback_feature_1_title' => ['en' => __('landing.collect_feedback', [], 'en'), 'ar' => __('landing.collect_feedback', [], 'ar')],
            'feedback_feature_1_desc' => ['en' => __('landing.collect_feedback_desc', [], 'en'), 'ar' => __('landing.collect_feedback_desc', [], 'ar')],
            'feedback_feature_2_title' => ['en' => __('landing.google_maps_boost', [], 'en'), 'ar' => __('landing.google_maps_boost', [], 'ar')],
            'feedback_feature_2_desc' => ['en' => __('landing.google_maps_feature', [], 'en') . ' ' . __('landing.google_maps_desc', [], 'en'), 'ar' => __('landing.google_maps_feature', [], 'ar') . ' ' . __('landing.google_maps_desc', [], 'ar')],
            'feedback_feature_3_title' => ['en' => __('landing.insights', [], 'en'), 'ar' => __('landing.insights', [], 'ar')],
            'feedback_feature_3_desc' => ['en' => __('landing.insights_desc', [], 'en'), 'ar' => __('landing.insights_desc', [], 'ar')],
        ];

        // START CHANGE: Use explicit loop to merge to avoid overwriting arrays with nulls if using simple array_merge
        // But here we want DB settings to override defaults.
        // Convert DB settings to array first
        $settings = $dbSettings->toArray();
        foreach ($defaults as $key => $defaultVal) {
            if (!isset($settings[$key])) {
                $settings[$key] = $defaultVal;
            }
        }
        // END CHANGE

        $screenshots = \App\Models\LandingScreenshot::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        return Inertia::render('Welcome', [
            'plans' => $plans,
            'modules' => $modules,
            'deliveryProviders' => $deliveryProviders,
            'landingSettings' => $settings,
            'screenshots' => $screenshots,
        ]);
    }

    public function registerInterest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan_id' => 'required', // ID or slug
            'plan_name' => 'required|string',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'restaurant_name' => 'required|string|max:255',
            'message' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        // Save to Database
        try {
            \App\Models\PlanInterest::create($request->all());
        } catch (\Exception $e) {
            \Log::error('Registration Interest Database Error: ' . $e->getMessage());
        }

        $contactEmail = \App\Models\SystemConfiguration::get('registration_email')
            ?? LandingSetting::get('interest_notification_email')
            ?? LandingSetting::get('contact_email', 'support@kenildock.com');

        // Send Email
        try {
            \Log::info("Attempting to send Registration Interest email to: " . $contactEmail);
            Mail::to($contactEmail)->send(new RegistrationInterest($request->all()));

            // MANUAL LOG
            \App\Services\CommunicationService::log([
                'recipient' => $contactEmail,
                'type' => 'email',
                'status' => 'sent',
                'subject' => 'New Registration Interest',
                'message' => "New lead from {$request->restaurant_name} ({$request->name})",
            ]);

            \Log::info("Registration Interest email sent successfully.");
        } catch (\Exception $e) {
            \Log::error('Registration Interest Email Error: ' . $e->getMessage());

            // MANUAL LOG FAILURE
            \App\Services\CommunicationService::log([
                'recipient' => $contactEmail,
                'type' => 'email',
                'status' => 'failed',
                'subject' => 'New Registration Interest',
                'error_message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to send email. Please try again or contact us directly.');
        }

        return back()->with('success', 'thank_you_message');
    }
}
