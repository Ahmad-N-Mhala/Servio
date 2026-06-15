<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\SystemConfiguration;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserManualController extends Controller
{
    /**
     * Display the user manual.
     */
    public function index(Request $request): Response
    {
        $content = SystemConfiguration::get('user_manual_content');

        if (! $content) {
            $content = $this->getDefaultManualContent();
        }

        return Inertia::render('UserManual/Index', [
            'manualContent' => $content,
        ]);
    }

    /**
     * Update the user manual content.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'sections' => ['required', 'array'],
            'sections.*.id' => ['required', 'string'],
            'sections.*.title' => ['required', 'array'],
            'sections.*.title.en' => ['required', 'string'],
            'sections.*.title.ar' => ['required', 'string'],
            'sections.*.description' => ['required', 'array'],
            'sections.*.description.en' => ['required', 'string'],
            'sections.*.description.ar' => ['required', 'string'],
            'sections.*.steps' => ['required', 'array'],
            'sections.*.steps.*.en' => ['required', 'string'],
            'sections.*.steps.*.ar' => ['required', 'string'],
            'faqs' => ['required', 'array'],
            'faqs.*.question' => ['required', 'array'],
            'faqs.*.question.en' => ['required', 'string'],
            'faqs.*.question.ar' => ['required', 'string'],
            'faqs.*.answer' => ['required', 'array'],
            'faqs.*.answer.en' => ['required', 'string'],
            'faqs.*.answer.ar' => ['required', 'string'],
        ]);

        SystemConfiguration::set('user_manual_content', $validated);

        return back()->with('message', 'User manual content updated successfully.');
    }

    /**
     * Get the default bilingual manual content.
     */
    private function getDefaultManualContent(): array
    {
        return [
            'sections' => [
                [
                    'id' => 'pos',
                    'title' => [
                        'en' => 'POS & Register Settle Workspace',
                        'ar' => 'نقاط البيع وتسوية الفواتير',
                    ],
                    'description' => [
                        'en' => 'The Point of Sale (POS) workspace manages cashier operations, register opening/closing, deposits, withdrawals, and direct bill settlements.',
                        'ar' => 'تدير مساحة عمل نقاط البيع (POS) عمليات أمين الصندوق، فتح وإغلاق الصندوق، الإيداعات، السحوبات، وتسوية الفواتير مباشرة.',
                    ],
                    'steps' => [
                        [
                            'en' => 'Open Register: Navigate to POS > Open Register. Input the opening cash float balance (e.g., 500.00 AED) and optional notes. Click Open Register.',
                            'ar' => 'فتح الصندوق: انتقل إلى نقاط البيع > فتح الصندوق. أدخل رصيد الافتتاح النقدي (مثال: 500 درهم) وملاحظات اختيارية، ثم انقر فتح الصندوق.',
                        ],
                        [
                            'en' => 'Settle Order: In the POS screen, select an active order from the sidebar list. Review items, apply discount or extra charge if permitted, choose payment method (Cash, Card, or Online), and click Settle.',
                            'ar' => 'تسوية الطلب: في شاشة نقاط البيع، اختر طلباً نشطاً من قائمة الشريط الجانبي. راجع المنتجات، طبق خصماً أو رسوماً إضافية إذا كانت مسموحة، اختر طريقة الدفع (نقدي، بطاقة، أونلاين)، ثم انقر تسوية.',
                        ],
                        [
                            'en' => 'Deposit / Withdraw Cash: Click Settle or Register controls, select Deposit or Withdraw. Enter cash amount, write description explaining the transaction, and click Settle/Submit.',
                            'ar' => 'إيداع / سحب النقد: انقر على خيارات الصندوق، اختر إيداع أو سحب. أدخل المبلغ النقدي، اكتب سبباً يوضح المعاملة، ثم انقر تسوية / إرسال.',
                        ],
                        [
                            'en' => 'Close Register: At the end of the shift, go to Register, click Close Register. Review opening balance, expected cash sales, expected closing balance. Enter the actual cash present, write closing notes, and click Close.',
                            'ar' => 'إغلاق الصندوق: في نهاية الوردية، انقر على إغلاق الصندوق. راجع رصيد الافتتاح، مبيعات النقد المتوقعة، رصيد الإغلاق المتوقع. أدخل الرصيد الفعلي الموجود بالصندوق، اكتب الملاحظات، ثم انقر إغلاق.',
                        ]
                    ]
                ],
                [
                    'id' => 'orders',
                    'title' => [
                        'en' => 'Order Management & Delivery',
                        'ar' => 'إدارة الطلبات والتوصيل',
                    ],
                    'description' => [
                        'en' => 'Create dine-in, takeaway, and delivery orders. Integrate external delivery platforms and manage cooking state via KDS.',
                        'ar' => 'إنشاء طلبات المحلي والسفري والتوصيل. ربط منصات التوصيل الخارجية وإدارة حالة التحضير عبر شاشة المطبخ KDS.',
                    ],
                    'steps' => [
                        [
                            'en' => 'Create Back-office Order: Go to Operations > Orders > New Order. Select Type (Dine-in with table selection, Takeaway, or Delivery), choose customer details, add menu items/combos, write optional item notes, and submit.',
                            'ar' => 'إنشاء طلب إداري: انتقل إلى العمليات > الطلبات > طلب جديد. اختر النوع (محلي مع تحديد الطاولة، سفري، أو توصيل)، اختر بيانات العميل، أضف عناصر القائمة، اكتب ملاحظات واضغط إرسال.',
                        ],
                        [
                            'en' => 'External Orders: Noon, Talabat, Deliveroo, and Careem orders appear automatically in the order list. Manage status lifecycle or print receipts directly.',
                            'ar' => 'الطلبات الخارجية: تظهر طلبات نون وتلابات ودليفرو وكريم تلقائياً في قائمة الطلبات. يمكنك إدارة دورة حالة الطلب أو طباعة الإيصالات مباشرة.',
                        ],
                        [
                            'en' => 'Thermal Receipt: Select Actions > View Receipt (Thermal) on any order to preview and print receipt.',
                            'ar' => 'إيصال حراري: اختر إجراءات > عرض الإيصال (حراري) على أي طلب لمعاينة وطباعة إيصال الفاتورة.',
                        ]
                    ]
                ],
                [
                    'id' => 'menu',
                    'title' => [
                        'en' => 'Menu & Recipe Setup',
                        'ar' => 'إعداد قائمة الطعام والوصفات',
                    ],
                    'description' => [
                        'en' => 'Organize categories, create menu items with combo bundles or custom extras, and map ingredient recipes for auto-deductions.',
                        'ar' => 'تنظيم الفئات، إنشاء عناصر قائمة الطعام مع عروض الكومبو أو الإضافات المخصصة، وربط وصفات المكونات للخصم التلقائي.',
                    ],
                    'steps' => [
                        [
                            'en' => 'Manage Categories: Go to Management > Restaurant > Menu. Click Add Category to create new sections. Edit sort orders or rename in English and Arabic.',
                            'ar' => 'إدارة الفئات: انتقل إلى الإدارة > المطعم > قائمة الطعام. انقر إضافة فئة لإنشاء أقسام جديدة. رتب الفئات أو غير أسمائها بالإنجليزية والعربية.',
                        ],
                        [
                            'en' => 'Create Item & Extras: Select category, click Add Item. Input price, name, upload image. Configure extra groups (e.g. Cheese toppings: +2.00 AED) and select child bundles.',
                            'ar' => 'إنشاء عنصر وإضافات: اختر الفئة، انقر إضافة عنصر. حدد السعر، الاسم، وارفع صورة. قم بإعداد مجموعات الإضافات (مثال: جبنة إضافية: +2 درهم) وحدد عروض الكومبو.',
                        ],
                        [
                            'en' => 'Recipe Mapping: Under item settings, map required ingredient quantities (e.g., Hamburger Bun: 1 pcs, Beef Patty: 150g). Stock will deduct automatically when orders begin cooking.',
                            'ar' => 'ربط المكونات بالوصفة: تحت إعدادات العنصر، اربط كميات المكونات المطلوبة (مثال: خبز هامبرغر: 1 قطعة، شريحة لحم: 150 جرام). سيتم خصم المخزون تلقائياً عند بدء تحضير الطلب.',
                        ]
                    ]
                ],
                [
                    'id' => 'inventory',
                    'title' => [
                        'en' => 'Inventory & Waste Management',
                        'ar' => 'إدارة المخزون وتتبع الهدر',
                    ],
                    'description' => [
                        'en' => 'Monitor raw ingredients stock, record inbound shipment batches via FIFO cost tracking, and log wastage details.',
                        'ar' => 'مراقبة مخزون المكونات الخام، تسجيل دفعات الشحن الواردة باستخدام تتبع تكلفة الوارد أولاً يصرف أولاً (FIFO)، وتسجيل تفاصيل الهدر.',
                    ],
                    'steps' => [
                        [
                            'en' => 'Add Inbound Stock: Navigate to Operations > Inventory > Add Stock. Input ingredient details, quantity, cost per unit, batch number, and expiration date.',
                            'ar' => 'إضافة كميات للمخزون: انتقل إلى العمليات > المخزون > إضافة كمية. أدخل تفاصيل المكون، الكمية، تكلفة الوحدة، رقم الدفعة، وتاريخ انتهاء الصلاحية.',
                        ],
                        [
                            'en' => 'Log Waste: Go to Operations > Waste > Log Waste. Select ingredient, choose source batch, quantity wasted, and write loss reason (e.g., Expired or Damaged).',
                            'ar' => 'تسجيل هدر: انتقل إلى العمليات > الهدر > تسجيل هدر. اختر المكون، دفعة المصدر، الكمية المهدرة، واكتب سبب الخسارة (مثال: تالف أو منتهي الصلاحية).',
                        ],
                        [
                            'en' => 'Restore Waste Log: Open Waste dashboard, click Actions next to the log record, and select Restore to return quantities back to the stock batch.',
                            'ar' => 'استعادة هدر: افتح لوحة تحكم الهدر، انقر إجراءات بجوار السجل، واختر استعادة لإرجاع الكميات المهدرة إلى دفعة المخزون الأصلية.',
                        ]
                    ]
                ],
                [
                    'id' => 'loyalty',
                    'title' => [
                        'en' => 'Loyalty Program & OTP Redemptions',
                        'ar' => 'برنامج الولاء واسترداد النقاط عبر OTP',
                    ],
                    'description' => [
                        'en' => 'Enroll members, configure reward tiers, customize loyalty card designs, and verify redemptions securely using SMS OTP codes.',
                        'ar' => 'تسجيل الأعضاء، إعداد فئات المكافآت، تخصيص تصميم بطاقة الولاء، والتحقق الآمن من عمليات الاسترداد باستخدام كود OTP SMS.',
                    ],
                    'steps' => [
                        [
                            'en' => 'Register Member: Navigate to Growth > Customers > Add Customer. Enter client name, phone number, and birth date to enroll.',
                            'ar' => 'تسجيل عضو: انتقل إلى النمو > العملاء > إضافة عميل. أدخل اسم العميل، رقم الهاتف، وتاريخ الميلاد لإدراجه في البرنامج.',
                        ],
                        [
                            'en' => 'Setup Reward Design: Go to Loyalty Settings. Customize card background banner and brand logo. Super admins can upload or delete these logos and banners separately.',
                            'ar' => 'إعداد تصميم البطاقة: انتقل إلى إعدادات الولاء. قم بتخصيص شعار الماركة وخلفية البطاقة. يمكن للآدمن رفع أو حذف الشعار والخلفية بشكل منفصل.',
                        ],
                        [
                            'en' => 'Redeem Points: During checkout in POS, select Redeem Reward. Choose reward tier, click Send SMS. Request the 6-digit verification code sent to customer\'s phone, verify code, and complete transaction.',
                            'ar' => 'استرداد النقاط: أثناء الدفع في نقاط البيع، اختر استرداد المكافأة. حدد فئة المكافأة، انقر إرسال رسالة كود التحقق. اطلب الكود المكون من 6 أرقام المستلم على هاتف العميل، تحقق منه، ثم أكمل المعاملة.',
                        ]
                    ]
                ]
            ],
            'faqs' => [
                [
                    'question' => [
                        'en' => 'How does recipe stock deduction work?',
                        'ar' => 'كيف تعمل عملية خصم مخزون الوصفة؟',
                    ],
                    'answer' => [
                        'en' => 'When a kitchen display operator moves an order to Preparing or Completed, the system calculates the ingredient quantities mapped in the item recipe and deducts them from active batches using FIFO.',
                        'ar' => 'عند قيام موظف المطبخ بتحويل حالة الطلب إلى (قيد التحضير) أو (مكتمل)، يقوم النظام بحساب كميات المكونات المحددة في وصفة العنصر ويخصمها من الدفعات النشطة بالصندوق باستخدام FIFO.',
                    ],
                ],
                [
                    'question' => [
                        'en' => 'Can I delete uploaded loyalty banners or logos?',
                        'ar' => 'هل يمكنني حذف خلفيات أو شعارات بطاقة الولاء المرفوعة؟',
                    ],
                    'answer' => [
                        'en' => 'Yes, in the Loyalty Card Designer settings page, there are separate delete buttons for both the brand logo and the background card banner.',
                        'ar' => 'نعم، في صفحة مصمم بطاقة الولاء، تتوفر أزرار حذف منفصلة لكل من شعار الماركة وخلفية البطاقة.',
                    ],
                ],
                [
                    'question' => [
                        'en' => 'Why does register closing show a difference?',
                        'ar' => 'لماذا يظهر فرق عند إغلاق الصندوق؟',
                    ],
                    'answer' => [
                        'en' => 'The expected register balance calculations count all cash sales, expected opening balance, and manual deposits/withdrawals. If actual cash entered at close differs, the difference is logged.',
                        'ar' => 'تحسب قيمة رصيد الصندوق المتوقعة كافة مبيعات الكاش، الرصيد الافتتاحي، والإيداعات والسحوبات اليدوية. إذا اختلف الرصيد الفعلي المدخل عند الإغلاق عن المتوقع، يتم تسجيل هذا الفرق في التقارير.',
                    ],
                ]
            ]
        ];
    }
}
