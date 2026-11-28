<?php

// The invoice PDF itself is Dutch or English (PDF tools cannot shape Arabic
// letters); these keys are for the shop pages and the admin.
return [
    'title' => 'فاتورة',
    'download' => 'تحميل الفاتورة :number (PDF)',
    'admin_missing' => 'تنقص الفواتير بيانات شركة يفرضها القانون الهولندي: :fields. أضفها في ملف ‎.env على السيرفر.',
    'credit' => [
        'title' => 'فاتورة دائنة',
        'download' => 'الفاتورة الدائنة :number (PDF)',
    ],
    'export' => [
        'title' => 'تصدير الطلبات (Excel)',
        'from' => 'من',
        'to' => 'إلى',
        'status' => 'الحالة',
        'all' => 'الكل ما عدا الملغاة',
        'button' => 'تحميل ‎.xlsx',
    ],
];
