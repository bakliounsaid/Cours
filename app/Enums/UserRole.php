<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum UserRole: string
{
    use HasLabel;

    case Admin = 'admin';
    case Teacher = 'teacher';

    public function homeRoute(): string
    {
        return match ($this) {
            self::Admin => 'admin.dashboard',
            self::Teacher => 'teacher.today',
        };
    }
}
