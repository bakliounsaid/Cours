<?php

namespace App\Actions\Lessons;

use App\Enums\LessonEventType;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Enregistre le travail réalisé pendant la séance et les devoirs donnés.
 * Chaque modification est conservée dans le journal (avec les anciennes valeurs).
 */
class RecordLessonWork
{
    public function handle(Lesson $lesson, ?string $summary, ?string $homework, User $by): void
    {
        $summary = filled($summary) ? trim($summary) : null;
        $homework = filled($homework) ? trim($homework) : null;

        if ($summary === $lesson->summary && $homework === $lesson->homework) {
            return;
        }

        DB::transaction(function () use ($lesson, $summary, $homework, $by) {
            $lesson->events()->create([
                'user_id' => $by->id,
                'type' => LessonEventType::WorkRecorded,
                'data' => [
                    'summary' => $summary,
                    'homework' => $homework,
                    'previous_summary' => $lesson->summary,
                    'previous_homework' => $lesson->homework,
                ],
            ]);

            $lesson->update(['summary' => $summary, 'homework' => $homework]);
        });
    }
}
