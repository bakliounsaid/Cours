<?php

return [
    'user_role' => [
        'admin' => 'مدير',
        'teacher' => 'أستاذ',
    ],
    'cycle' => [
        'primary' => 'ابتدائي',
        'middle' => 'متوسط',
    ],
    'lesson_type' => [
        'group' => 'فوج',
        'individual' => 'فردي',
        'special' => 'خاصة',
        'replacement' => 'تعويض أستاذ',
        'makeup' => 'استدراك',
    ],
    'lesson_status' => [
        'scheduled' => 'مبرمجة',
        'done' => 'منجزة',
        'cancelled' => 'ملغاة',
        'needs_action' => 'تحتاج معالجة',
    ],
    'attendance_status' => [
        'present' => 'حاضر',
        'absent' => 'غائب',
        'late' => 'متأخر',
    ],
    'payment_method' => [
        'cash' => 'نقدًا',
        'transfer' => 'تحويل',
        'other' => 'أخرى',
    ],
    'payment_status' => [
        'up_to_date' => 'مسدد',
        'reminder_soon' => 'الدفع قريبًا',
        'due' => 'الدفع مستحق',
        'overdue' => 'متأخر',
    ],
    'enrollment_status' => [
        'new' => 'جديد',
        'processed' => 'تمت المعالجة',
    ],
    'absence_scope' => [
        'lesson' => 'حصة واحدة',
        'day' => 'اليوم كاملًا',
    ],
    'absence_resolution' => [
        'cancelled' => 'ملغاة',
        'replaced' => 'معوَّضة',
        'rescheduled' => 'مؤجلة',
    ],
    'message_channel' => [
        'sms' => 'رسالة نصية',
        'whatsapp' => 'واتساب',
    ],
    'parent_message_type' => [
        'lesson_cancelled' => 'إلغاء حصة',
        'lesson_rescheduled' => 'تأجيل حصة',
        'lesson_added' => 'إضافة حصة',
        'payment_reminder' => 'تذكير بالدفع',
    ],
    'locale' => [
        'fr' => 'الفرنسية',
        'ar' => 'العربية',
    ],
];
