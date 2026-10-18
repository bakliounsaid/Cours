<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum AbsenceScope: string
{
    use HasLabel;

    case Lesson = 'lesson';
    case Day = 'day';
}
