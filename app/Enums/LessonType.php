<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum LessonType: string
{
    use HasLabel;

    case Group = 'group';
    case Individual = 'individual';
    case Special = 'special';
    case Replacement = 'replacement';
    case Makeup = 'makeup';

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Group => 'bg-olive-50 text-olive-700',
            self::Individual => 'bg-honey-100 text-honey-700',
            self::Special => 'bg-violet-50 text-violet-700',
            self::Replacement => 'bg-terra-50 text-terra-700',
            self::Makeup => 'bg-emerald-50 text-emerald-700',
        };
    }
}
