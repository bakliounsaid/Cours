<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum LessonEventType: string
{
    use HasLabel;

    /** Appel validé : pointage de l'enseignant et présences initiales. */
    case CheckedIn = 'checked_in';

    /** Présences modifiées après la validation de l'appel. */
    case AttendanceUpdated = 'attendance_updated';

    /** Travail réalisé / devoirs saisis ou modifiés. */
    case WorkRecorded = 'work_recorded';

    public function icon(): string
    {
        return match ($this) {
            self::CheckedIn => 'clipboard-check',
            self::AttendanceUpdated => 'pencil',
            self::WorkRecorded => 'book-open',
        };
    }
}
