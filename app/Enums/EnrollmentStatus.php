<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum EnrollmentStatus: string
{
    use HasLabel;

    case New = 'new';
    case Processed = 'processed';
}
