<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum PaymentStatus: string
{
    use HasLabel;

    case UpToDate = 'up_to_date';
    case ReminderSoon = 'reminder_soon';
    case Due = 'due';
    case Overdue = 'overdue';

    public function needsAttention(): bool
    {
        return in_array($this, [self::Due, self::Overdue], true);
    }

    /** Couleur de la jauge du cycle de séances. */
    public function progressClasses(): string
    {
        return match ($this) {
            self::UpToDate => 'bg-sage-500',
            self::ReminderSoon => 'bg-honey-500',
            self::Due => 'bg-terra-500',
            self::Overdue => 'bg-red-600',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::UpToDate => 'bg-emerald-50 text-emerald-700',
            self::ReminderSoon => 'bg-honey-100 text-honey-700',
            self::Due => 'bg-terra-50 text-terra-700',
            self::Overdue => 'bg-red-100 text-red-700',
        };
    }
}
