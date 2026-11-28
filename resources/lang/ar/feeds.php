<?php

return [
    'channel_description' => 'منتجات :shop',
    'admin' => [
        'title' => 'كتالوج المنتجات',
        'subtitle' => 'روابط فيها كل منتجاتك لـ Google Shopping ولمتجر Instagram / Facebook. المنصتان تقرآنها بنفسيهما، فتبقى الأسعار والتخفيضات والمخزون محدّثة.',
        'links' => 'روابط الكتالوج (رابط لكل لغة)',
        'copy' => 'نسخ',
        'open' => 'فتح',
        'count' => 'عدد المنتجات في الكتالوج: :count (المنتج ذو المقاسات/الألوان يظهر مرة لكل مقاس/لون).',
        'without_photo' => 'منتجات ظاهرة بلا صورة لا تُضاف إلى الكتالوج (المنصتان تشترطان صورة)، وعددها :count:',
        'google_steps' => [
            'ادخل إلى merchants.google.com وأنشئ حساباً مجانياً لمتجرك (الدولة: هولندا، العملة: EUR).',
            'أكّد ملكية موقعك (rukn.nl) من Settings ← Business info.',
            'Products ← Add products ← Add products from a file ← "Add a file from a link" (جلب مجدول).',
            'الصق رابط الكتالوج الهولندي واختر "يومياً".',
            'راجع مدة التوصيل في Shipping؛ الكتالوج يرسل تكاليف الشحن داخل هولندا تلقائياً.',
            'بعد أول جلب، افحص "Needs attention" في Products لمعرفة المنتجات المرفوضة.',
        ],
        'meta_steps' => [
            'ادخل إلى business.facebook.com ← Commerce Manager وأنشئ كتالوجاً (النوع: e-commerce).',
            'Catalogue ← Data sources ← Add items ← Data feed ← "Use a URL".',
            'الصق رابط الكتالوج (الهولندي أو لغة متابعيك)، واختر العملة EUR والتحديث كل يوم.',
            'اربط الكتالوج بحساب Instagram من Commerce Manager ← Settings، ثم فعّل وسم المنتجات في Instagram.',
            'Meta تراجع المتجر أولاً؛ بعد الموافقة تستطيع وسم المنتجات في المنشورات والقصص.',
        ],
        'notes' => 'الأسعار شاملة الضريبة. التخفيضات تُرسل مع تواريخها. المنتجات المخفية لا تُضاف أبداً. لا يُرسل الجنس ولا الفئة العمرية: أضفهما في Merchant Center إذا طلبهما Google للملابس.',
    ],
];
