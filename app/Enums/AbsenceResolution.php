<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum AbsenceResolution: string
{
    use HasLabel;

    case Cancelled = 'cancelled';
    case Replaced = 'replaced';
    case Rescheduled = 'rescheduled';
}
