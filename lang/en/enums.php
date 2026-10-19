<?php

return [
    'user_role' => [
        'admin' => 'Administrator',
        'teacher' => 'Teacher',
    ],
    'cycle' => [
        'primary' => 'Primary',
        'middle' => 'Middle school (CEM)',
    ],
    'lesson_type' => [
        'group' => 'Group',
        'individual' => 'One-to-one',
        'special' => 'Special',
        'replacement' => 'Replacement',
        'makeup' => 'Make-up',
    ],
    'lesson_status' => [
        'scheduled' => 'Scheduled',
        'done' => 'Done',
        'cancelled' => 'Cancelled',
        'needs_action' => 'To handle',
    ],
    'attendance_status' => [
        'present' => 'Present',
        'absent' => 'Absent',
        'late' => 'Late',
    ],
    'payment_method' => [
        'cash' => 'Cash',
        'transfer' => 'Bank transfer',
        'other' => 'Other',
    ],
    'payment_status' => [
        'up_to_date' => 'Up to date',
        'reminder_soon' => 'Payment soon',
        'due' => 'Payment due',
        'overdue' => 'Overdue',
    ],
    'enrollment_status' => [
        'new' => 'New',
        'processed' => 'Processed',
    ],
    'absence_scope' => [
        'lesson' => 'One lesson',
        'day' => 'Whole day',
    ],
    'absence_resolution' => [
        'cancelled' => 'Cancelled',
        'replaced' => 'Replaced',
        'rescheduled' => 'Rescheduled',
    ],
    'message_channel' => [
        'sms' => 'SMS',
        'whatsapp' => 'WhatsApp',
    ],
    'parent_message_type' => [
        'lesson_cancelled' => 'Lesson cancelled',
        'lesson_rescheduled' => 'Lesson rescheduled',
        'lesson_added' => 'Lesson added',
        'payment_reminder' => 'Payment reminder',
    ],
    'locale' => [
        'fr' => 'French',
        'ar' => 'Arabic',
        'en' => 'English',
    ],
];
