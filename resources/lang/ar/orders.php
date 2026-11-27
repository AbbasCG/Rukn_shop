<?php

return [
    'title' => 'طلباتي',
    'subtitle' => 'عرض مشترياتك الأخيرة وحالتها.',

    'empty' => [
        'title' => 'لم تقم بتقديم أي طلبات حتى الآن.',
        'subtitle' => 'ابدأ التسوق لرؤية طلباتك هنا.',
        'button' => 'ابدأ التسوق',
    ],

    'table' => [
        'order_number' => 'الطلب #',
        'date' => 'التاريخ',
        'total' => 'الإجمالي',
        'status' => 'الحالة',
        'actions' => 'الإجراءات',
    ],

    'status' => [
        'pending' => 'قيد الانتظار',
        'paid' => 'مدفوع',
        'processing' => 'قيد المعالجة',
        'shipped' => 'تم الإرسال',
        'delivered' => 'تم التسليم',
        'cancelled' => 'ملغى',
        'refunded' => 'مسترجع',
    ],

    'actions' => [
        'view_details' => 'عرض التفاصيل',
        'back_to_orders' => 'العودة إلى طلباتي',
        'back_to_profile' => 'العودة إلى الملف الشخصي',
        'continue_shopping' => 'المتابعة إلى التسوق',
    ],

    'details' => [
        'title' => 'تفاصيل الطلب',
        'summary' => 'ملخص الطلب',
        'shipping_info' => 'معلومات الشحن',
        'items' => 'عناصر الطلب',
        'status_tracking' => 'حالة الطلب',
        'notes' => 'ملاحظات الطلب',
    ],

    'summary' => [
        'subtotal' => 'المجموع الجزئي',
        'shipping' => 'الشحن',
        'tax' => 'الضريبة',
        'discount' => 'الخصم',
        'total' => 'الإجمالي',
        'payment_method' => 'طريقة الدفع',
        'payment_status' => 'حالة الدفع',
    ],

    'shipping' => [
        'name' => 'الاسم',
        'address' => 'العنوان',
        'postal_code' => 'الرمز البريدي',
        'city' => 'المدينة',
        'country' => 'الدولة',
        'phone' => 'رقم الهاتف',
    ],

    'items' => [
        'product' => 'المنتج',
        'product_unavailable' => 'المنتج غير متوفر',
        'sku' => 'رمز المنتج',
        'quantity' => 'الكمية',
        'price_per_unit' => 'السعر لكل وحدة',
        'item_total' => 'إجمالي العنصر',
        'no_items' => 'لم يتم العثور على عناصر في هذا الطلب.',
    ],

    'tracking' => [
        'title' => 'حالة الطلب',
        'live_tracking' => 'التتبع المباشر',
        'current_status' => 'الحالة الحالية',
        'in_progress' => 'قيد المعالجة',
        'update_message' => 'سنقوم بإبلاغك عندما ينتقل طلبك إلى الخطوة التالية.',
        'steps' => [
            'pending' => 'قيد الانتظار',
            'paid' => 'مدفوع',
            'processing' => 'قيد المعالجة',
            'shipped' => 'تم الإرسال',
            'delivered' => 'تم التسليم',
        ],
    ],

    'confirmation' => [
        'thank_you' => 'شكراً على طلبك!',
        'received' => 'تم استلام طلبك وهو قيد المعالجة الآن.',
        'summary' => 'ملخص الطلب',
        'order_number' => 'الطلب #',
    ],
    'lookup' => [
        'title' => 'ابحث عن طلبي',
        'subtitle' => 'أدخل رقم الطلب والإيميل الذي استخدمته عند الشراء.',
        'number' => 'رقم الطلب',
        'number_placeholder' => 'مثلاً 000123',
        'email' => 'البريد الإلكتروني',
        'submit' => 'اعرض طلبي',
        'not_found' => 'لم نجد طلباً بهذا الرقم وهذا الإيميل.',
    ],
    'guest' => [
        'save_link' => 'احفظ هذه الصفحة: رابطها هو طريقتك لرؤية طلبك بدون حساب. وأرسلناه أيضاً إلى :email.',
        'find_later' => 'أضعت الرابط؟ يمكنك الوصول لطلبك برقمه وإيميلك.',
        'find_link' => 'ابحث عن طلبي',
        'create_account' => 'تريد رؤية كل طلباتك في مكان واحد؟ أنشئ حساباً بنفس الإيميل.',
    ],
    'tracking' => [
        'title' => 'الشحن',
        'shipped_on' => 'تم الشحن في :date',
        'number' => 'رقم التتبّع',
        'carrier_number' => 'رقم التتبّع لدى :carrier',
        'track' => 'تتبّع طردك',
        'no_tracking' => 'هذا الطرد مُرسل بدون رقم تتبّع.',
    ],
];
