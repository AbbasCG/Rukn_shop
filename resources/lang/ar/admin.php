<?php

return [
    'sidebar' => [
        'brand' => 'لوحة الإدارة',
        'sections' => [
            'menu' => 'القائمة',
            'coming_soon' => 'قريبًا',
        ],
        'items' => [
            'dashboard' => 'لوحة التحكم',
            'products' => 'المنتجات',
            'categories' => 'التصنيفات',
            'orders' => 'الطلبات',
            'users' => 'المستخدمون',
            'settings' => 'الإعدادات',
            'reports' => 'التقارير',
            'logout' => 'تسجيل الخروج',
        ],
        'tooltips' => [
            'close_sidebar' => 'إغلاق الشريط الجانبي',
        ],
    ],
    'footer' => [
        'logged_in_as' => 'مسجل الدخول كـ',
        'back_to_shop' => 'العودة إلى المتجر',
        'rights' => 'جميع الحقوق محفوظة',
        'powered_by' => 'مدعوم بواسطة :name',
        'links' => [
            'privacy' => 'سياسة الخصوصية',
            'terms' => 'الشروط والأحكام',
            'support' => 'الدعم',
            'docs' => 'التوثيق',
        ],
    ],
    'dashboard' => [
        'page_title' => 'لوحة التحكم',
        'title' => 'مرحبًا بعودتك، :name 👋',
        'cards' => [
            'total_products' => 'إجمالي المنتجات',
            'in_inventory' => 'في المخزون',
            'total_categories' => 'إجمالي التصنيفات',
            'active_categories' => 'التصنيفات النشطة',
            'total_orders' => 'إجمالي الطلبات',
            'all_time' => 'منذ البداية',
            'total_revenue' => 'إجمالي الإيرادات',
            'from_paid_orders' => 'من الطلبات المدفوعة',
        ],
        'sales' => [
            'title' => 'المبيعات خلال آخر 12 شهرًا',
            'subtitle' => 'نظرة عامة على الإيرادات والاتجاهات',
            'chart_placeholder' => 'سيتم عرض الرسم البياني هنا',
        ],
        'quick_actions' => [
            'title' => 'إجراءات سريعة',
            'subtitle' => 'المهام الشائعة',
            'add_new_product' => 'إضافة منتج جديد',
            'add_new_category' => 'إضافة تصنيف جديد',
            'add_new_user' => 'إضافة مستخدم جديد',
            'view_all_orders' => 'عرض كل الطلبات',
        ],
        'low_stock' => [
            'title' => 'تنبيهات انخفاض المخزون',
            'items_left' => 'متبقي :count',
            'all_well_stocked' => '✓ جميع المنتجات متوفرة بشكل جيد',
        ],
        'recent_orders' => [
            'title' => 'أحدث الطلبات',
            'subtitle' => 'آخر طلبات العملاء',
            'guest' => 'زائر',
            'view_action' => 'عرض',
            'empty' => 'لا توجد طلبات بعد',
            'table' => [
                'order_id' => 'رقم الطلب',
                'customer' => 'العميل',
                'total' => 'الإجمالي',
                'status' => 'الحالة',
                'date' => 'التاريخ',
                'action' => 'إجراء',
            ],
        ],
        'statuses' => [
            'pending' => 'قيد الانتظار',
            'paid' => 'مدفوع',
            'shipped' => 'تم الشحن',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغي',
        ],
    ],
];
