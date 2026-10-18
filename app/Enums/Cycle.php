<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum Cycle: string
{
    use HasLabel;

    case Primary = 'primary';
    case Middle = 'middle';

    /** Nombre d'années du cycle (primaire : 5, CEM : 4). */
    public function years(): int
    {
        return match ($this) {
            self::Primary => 5,
            self::Middle => 4,
        };
    }
}
