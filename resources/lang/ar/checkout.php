<?php

return [
    'title' => 'إتمام الشراء',
    'subtitle' => 'أكمل بياناتك لإتمام الطلب.',

    'form' => [
        'name' => 'الاسم',
        'email' => 'البريد الإلكتروني',
        'phone' => 'رقم الهاتف',
        'country' => 'الدولة',
        'address1' => 'العنوان 1',
        'address2' => 'العنوان 2',
        'postal_code' => 'الرمز البريدي',
        'city' => 'المدينة',
        'shipping_method' => 'طريقة الشحن',
        'shipping_options' => [
            'flat' => 'سعر ثابت (مجاني)',
        ],
        'payment_method' => 'طريقة الدفع',
        'payment_options' => [
            'cod' => 'الدفع عند الاستلام',
            'mock' => 'دفع تجريبي',
        ],
        'submit' => 'تأكيد الطلب',
    ],

    'summary' => [
        'title' => 'ملخص الطلب',
        'subtotal' => 'المجموع الفرعي',
        'shipping' => 'الشحن',
        'total' => 'الإجمالي',
        'back_to_cart' => 'العودة إلى السلة',
    ],

    'flash' => [
        'empty_cart' => 'سلة التسوق فارغة.',
        'failed' => 'تعذر إتمام الطلب. يرجى المحاولة مرة أخرى.',
    ],

    'confirmation' => [
        'view_order' => 'عرض طلبي',
        'continue_shopping' => 'متابعة التسوق',
        'placed_with_number' => 'تم تقديم طلبك :number بنجاح.',
        'whatsapp_button' => 'إرسال الطلب عبر واتساب',
        'whatsapp' => [
            'greeting' => 'هلا، هذا ملخص طلبي:',
            'order_number' => 'رقم الطلب: :number',
            'customer_name' => 'اسم العميل: :value',
            'customer_email' => 'إيميل العميل: :value',
            'customer_phone' => 'جوال العميل: :value',
            'delivery_address' => 'عنوان التوصيل: :value',
            'items_title' => 'العناصر:',
            'item_line' => '- :name | الكمية: :qty | سعر الوحدة: :unit | الإجمالي: :subtotal',
            'total' => 'الإجمالي: :value',
            'payment_method' => 'طريقة الدفع: :value',
            'order_link_label' => 'رابط تفاصيل الطلب:',
        ],
    ],
];
