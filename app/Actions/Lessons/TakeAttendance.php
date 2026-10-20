<?php

namespace App\Actions\Lessons;

use App\Enums\AttendanceStatus;
use App\Enums\LessonEventType;
use App\Enums\LessonStatus;
use App\Enums\PaymentStatus;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\User;
use App\Notifications\PaymentDueNotification;
use App\Services\LessonCounter;
use App\Support\LessonBalance;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Appel d'une séance, fait au début par l'enseignant.
 *
 * - Première validation : pointage de l'enseignant (tarif figé), présences, séance « faite ».
 * - Validations suivantes : seules les présences changées sont enregistrées et tracées.
 * Chaque étape est inscrite dans le journal de la séance (lesson_events).
 * Un élève qui atteint son cycle de séances (ou le dépasse) déclenche une alerte de paiement.
 */
class TakeAttendance
{
    public function __construct(private readonly LessonCounter $counter) {}

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

        $before = $this->counter->forTeacher($lesson->teacher, $roster->keys());

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

        $this->notifyPayments($lesson, $roster, $before);
    }

    /**
     * Alerte l'enseignant pour chaque élève qui vient d'atteindre son cycle (paiement à demander)
     * ou de passer en retard. Pas de doublon : seul le changement de statut déclenche l'alerte.
     *
     * @param  Collection<int, Student>  $roster
     * @param  array<int, LessonBalance>  $before
     */
    private function notifyPayments(Lesson $lesson, Collection $roster, array $before): void
    {
        $after = $this->counter->forTeacher($lesson->teacher, $roster->keys());

        foreach ($after as $studentId => $balance) {
            $status = $balance->status();
            $previous = $before[$studentId]->status();

            $reached = $status->needsAttention() && ! $previous->needsAttention();
            $nowOverdue = $status === PaymentStatus::Overdue && $previous === PaymentStatus::Due;

            if ($reached || $nowOverdue) {
                $lesson->teacher->user->notify(new PaymentDueNotification($roster[$studentId], $status, $balance->unpaid()));
            }
        }
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
