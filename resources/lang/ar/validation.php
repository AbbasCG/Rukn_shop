<?php

/*
| Arabic validation messages. Without this file Laravel fell back to its
| built-in English messages on Arabic pages.
*/

return [
    'accepted' => 'يجب قبول :attribute.',
    'array' => 'يجب أن تكون قيمة :attribute قائمة.',
    'boolean' => 'يجب أن تكون قيمة :attribute نعم أو لا.',
    'confirmed' => 'تأكيد :attribute غير مطابق.',
    'current_password' => 'كلمة المرور غير صحيحة.',
    'date' => ':attribute ليس تاريخاً صحيحاً.',
    'digits' => 'يجب أن تتكون قيمة :attribute من :digits أرقام.',
    'email' => 'صيغة :attribute غير صحيحة.',
    'exists' => 'القيمة المختارة في :attribute غير موجودة.',
    'file' => 'يجب رفع ملف في :attribute.',
    'filled' => 'حقل :attribute مطلوب.',
    'image' => 'يجب أن يكون الملف في :attribute صورة.',
    'in' => 'القيمة المختارة في :attribute غير صالحة.',
    'integer' => 'يجب أن تكون قيمة :attribute عدداً صحيحاً.',
    'lowercase' => 'يجب أن تكون قيمة :attribute بأحرف صغيرة.',
    'max' => [
        'array' => 'يجب ألا تزيد عناصر :attribute عن :max.',
        'file' => 'يجب ألا يتجاوز حجم :attribute :max كيلوبايت.',
        'numeric' => 'يجب ألا تتجاوز قيمة :attribute :max.',
        'string' => 'يجب ألا يتجاوز طول :attribute :max حرفاً.',
    ],
    'mimes' => 'يجب أن يكون الملف في :attribute من نوع: :values.',
    'min' => [
        'array' => 'يجب ألا تقل عناصر :attribute عن :min.',
        'file' => 'يجب أن يكون حجم :attribute :min كيلوبايت على الأقل.',
        'numeric' => 'يجب أن تكون قيمة :attribute :min على الأقل.',
        'string' => 'يجب ألا يقل طول :attribute عن :min أحرف.',
    ],
    'numeric' => 'يجب أن تكون قيمة :attribute رقماً.',
    'password' => [
        'letters' => 'يجب أن تحتوي :attribute على حرف واحد على الأقل.',
        'mixed' => 'يجب أن تحتوي :attribute على حرف كبير وحرف صغير على الأقل.',
        'numbers' => 'يجب أن تحتوي :attribute على رقم واحد على الأقل.',
        'symbols' => 'يجب أن تحتوي :attribute على رمز واحد على الأقل.',
        'uncompromised' => 'ظهرت :attribute في تسريب بيانات. يرجى اختيار كلمة مرور أخرى.',
    ],
    'regex' => 'صيغة :attribute غير صحيحة.',
    'required' => 'حقل :attribute مطلوب.',
    'required_if' => 'حقل :attribute مطلوب عندما يكون :other :value.',
    'required_with' => 'حقل :attribute مطلوب عند وجود :values.',
    'same' => 'يجب أن تتطابق قيمة :attribute مع :other.',
    'size' => [
        'string' => 'يجب أن يكون طول :attribute :size أحرف.',
    ],
    'string' => 'يجب أن تكون قيمة :attribute نصاً.',
    'unique' => 'قيمة :attribute مستخدمة من قبل.',
    'uploaded' => 'فشل رفع :attribute.',
    'url' => 'صيغة الرابط في :attribute غير صحيحة.',

    'custom' => [],

    'attributes' => [
        'name' => 'الاسم',
        'email' => 'البريد الإلكتروني',
        'password' => 'كلمة المرور',
        'current_password' => 'كلمة المرور الحالية',
        'password_confirmation' => 'تأكيد كلمة المرور',
        'phone' => 'رقم الهاتف',
        'address' => 'العنوان',
        'address_line1' => 'العنوان',
        'address_line2' => 'العنوان (السطر الثاني)',
        'postal_code' => 'الرمز البريدي',
        'city' => 'المدينة',
        'country' => 'الدولة',
        'shipping_method' => 'طريقة الشحن',
        'payment_method' => 'طريقة الدفع',
        'quantity' => 'الكمية',
        'avatar' => 'الصورة الشخصية',
        'subject' => 'الموضوع',
        'message' => 'الرسالة',
    ],
];
