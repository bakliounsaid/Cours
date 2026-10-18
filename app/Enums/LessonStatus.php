<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum LessonStatus: string
{
    use HasLabel;

    case Scheduled = 'scheduled';
    case Done = 'done';
    case Cancelled = 'cancelled';
    case NeedsAction = 'needs_action';

    public function isActive(): bool
    {
        return $this !== self::Cancelled;
    }

    /** Classes Tailwind du badge */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Scheduled => 'bg-olive-50 text-olive-700',
            self::Done => 'bg-emerald-50 text-emerald-700',
            self::Cancelled => 'bg-gray-100 text-gray-500 line-through',
            self::NeedsAction => 'bg-terra-50 text-terra-700',
        };
    }
}
