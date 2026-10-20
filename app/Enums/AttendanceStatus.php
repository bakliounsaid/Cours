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

    /** Bouton sélectionné sur la feuille d'appel. */
    public function selectedClasses(): string
    {
        return match ($this) {
            self::Present => 'bg-emerald-600 text-white ring-emerald-600',
            self::Absent => 'bg-red-600 text-white ring-red-600',
            self::Late => 'bg-honey-500 text-ink ring-honey-500',
        };
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
