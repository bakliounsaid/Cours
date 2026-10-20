<?php

namespace App\Actions\Lessons;

use App\Enums\AttendanceStatus;
use App\Enums\LessonEventType;
use App\Enums\LessonStatus;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Appel d'une séance, fait au début par l'enseignant.
 *
 * - Première validation : pointage de l'enseignant (tarif figé), présences, séance « faite ».
 * - Validations suivantes : seules les présences changées sont enregistrées et tracées.
 * Chaque étape est inscrite dans le journal de la séance (lesson_events).
 */
class TakeAttendance
{
    /**
     * @param  array<int|string, string>  $marks  [id élève => present|absent|late]
     *
     * @throws ValidationException si l'appel n'est pas ouvert ou incomplet
     */
    public function handle(Lesson $lesson, array $marks, User $by): void
    {
        if (! $lesson->canTakeAttendance()) {
            throw ValidationException::withMessages([
                'marks' => __('teacher.lessons.closed', ['time' => $lesson->attendanceOpensAt()->format('H:i')]),
            ]);
        }

        $roster = $lesson->roster()->keyBy('id');
        $statuses = [];

        foreach ($roster as $studentId => $student) {
            $status = AttendanceStatus::tryFrom((string) ($marks[$studentId] ?? ''));

            if (! $status) {
                throw ValidationException::withMessages([
                    'marks' => __('teacher.lessons.incomplete', ['name' => $student->full_name]),
                ]);
            }

            $statuses[$studentId] = $status;
        }

        DB::transaction(function () use ($lesson, $roster, $statuses, $by) {
            $existing = $lesson->attendances()->get()->keyBy('student_id');
            $changes = [];

            foreach ($statuses as $studentId => $status) {
                $previous = $existing->get($studentId);

                if ($previous?->status === $status) {
                    continue;
                }

                $lesson->attendances()->updateOrCreate(
                    ['student_id' => $studentId],
                    ['status' => $status, 'marked_by' => $by->id],
                );

                if ($previous) {
                    $changes[] = ['student' => $roster[$studentId]->full_name, 'from' => $previous->status->value, 'to' => $status->value];
                }
            }

            if (! $lesson->checkin()->exists()) {
                $this->checkIn($lesson, $statuses, $by);

                return;
            }

            if ($changes !== []) {
                $lesson->events()->create(['user_id' => $by->id, 'type' => LessonEventType::AttendanceUpdated, 'data' => ['changes' => $changes]]);
            }
        });
    }

    /**
     * @param  array<int, AttendanceStatus>  $statuses
     */
    private function checkIn(Lesson $lesson, array $statuses, User $by): void
    {
        $lesson->checkin()->create([
            'teacher_id' => $lesson->teacher_id,
            'checked_at' => now(),
            'rate_amount' => $lesson->teacher->rateFor($lesson),
        ]);

        $lesson->update(['status' => LessonStatus::Done]);

        $counts = array_count_values(array_map(fn (AttendanceStatus $status) => $status->value, $statuses));

        $lesson->events()->create([
            'user_id' => $by->id,
            'type' => LessonEventType::CheckedIn,
            'data' => [
                'present' => $counts[AttendanceStatus::Present->value] ?? 0,
                'late' => $counts[AttendanceStatus::Late->value] ?? 0,
                'absent' => $counts[AttendanceStatus::Absent->value] ?? 0,
            ],
        ]);
    }
}
