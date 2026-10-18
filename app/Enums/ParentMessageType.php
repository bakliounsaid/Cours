<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum ParentMessageType: string
{
    use HasLabel;

    case LessonCancelled = 'lesson_cancelled';
    case LessonRescheduled = 'lesson_rescheduled';
    case LessonAdded = 'lesson_added';
    case PaymentReminder = 'payment_reminder';
}
