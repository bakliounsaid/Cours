<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum AttendanceStatus: string
{
    use HasLabel;

    case Present = 'present';
    case Absent = 'absent';
    case Late = 'late';

    /** Un retard compte comme une présence. */
    public function isPresence(): bool
    {
        return $this !== self::Absent;
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Present => 'bg-emerald-50 text-emerald-700',
            self::Absent => 'bg-red-50 text-red-700',
            self::Late => 'bg-honey-100 text-honey-700',
        };
    }
}
