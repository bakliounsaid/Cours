<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Enums\LessonStatus;
use App\Models\Teacher;
use App\Settings\Settings;
use App\Support\LessonBalance;
use Illuminate\Support\Facades\DB;

/**
 * Nombre de séances de chaque élève chez un enseignant, et paiements correspondants.
 * Une séance compte quand l'appel l'a enregistrée : présent ou en retard
 * (et absent si le réglage « les absences consomment une séance » est actif).
 */
class LessonCounter
{
    public function __construct(private readonly Settings $settings) {}

    /**
     * @param  iterable<int>  $studentIds
     * @return array<int, LessonBalance> [id élève => compteur], en deux requêtes
     */
    public function forTeacher(Teacher $teacher, iterable $studentIds): array
    {
        $ids = collect($studentIds)->map(fn ($id) => (int) $id)->unique()->values()->all();

        if ($ids === []) {
            return [];
        }

        $statuses = array_map(
            fn (AttendanceStatus $status) => $status->value,
            array_filter(AttendanceStatus::cases(), fn (AttendanceStatus $status) => $status->isPresence() || $this->settings->countsAbsences()),
        );

        $attended = DB::table('attendances')
            ->join('lessons', 'lessons.id', '=', 'attendances.lesson_id')
            ->where('lessons.teacher_id', $teacher->id)
            ->where('lessons.status', '!=', LessonStatus::Cancelled->value)
            ->whereIn('attendances.student_id', $ids)
            ->whereIn('attendances.status', $statuses)
            ->groupBy('attendances.student_id')
            ->selectRaw('attendances.student_id as student_id, count(*) as total')
            ->pluck('total', 'student_id');

        $covered = DB::table('payments')
            ->where('teacher_id', $teacher->id)
            ->whereIn('student_id', $ids)
            ->groupBy('student_id')
            ->selectRaw('student_id, sum(lessons_covered) as total')
            ->pluck('total', 'student_id');

        $cycle = max(1, $this->settings->lessonsPerCycle());

        return collect($ids)->mapWithKeys(fn (int $id) => [$id => new LessonBalance(
            attended: (int) ($attended[$id] ?? 0),
            covered: (int) ($covered[$id] ?? 0),
            cycle: $cycle,
            preReminder: $this->settings->preReminderEnabled(),
        )])->all();
    }

    public function forStudent(Teacher $teacher, int $studentId): LessonBalance
    {
        return $this->forTeacher($teacher, [$studentId])[$studentId];
    }
}
