<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Models\Teacher;
use App\Repositories\Contracts\AttendanceRepository;
use App\Repositories\Contracts\PaymentRepository;
use App\Settings\Settings;
use App\Support\LessonBalance;

/**
 * Nombre de séances de chaque élève chez un enseignant, et paiements correspondants.
 * Une séance compte quand l'appel l'a enregistrée : présent ou en retard
 * (et absent si le réglage « les absences consomment une séance » est actif).
 */
class LessonCounter
{
    public function __construct(
        private readonly Settings $settings,
        private readonly AttendanceRepository $attendances,
        private readonly PaymentRepository $payments,
    ) {}

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

        $statuses = array_values(array_filter(
            AttendanceStatus::cases(),
            fn (AttendanceStatus $status) => $status->isPresence() || $this->settings->countsAbsences(),
        ));

        $attended = $this->attendances->countByStudentForTeacher($teacher, $ids, $statuses);
        $covered = $this->payments->lessonsCoveredByStudent($teacher, $ids);
        $cycle = max(1, $this->settings->lessonsPerCycle());

        return collect($ids)->mapWithKeys(fn (int $id) => [$id => new LessonBalance(
            attended: $attended[$id] ?? 0,
            covered: $covered[$id] ?? 0,
            cycle: $cycle,
            preReminder: $this->settings->preReminderEnabled(),
        )])->all();
    }

    public function forStudent(Teacher $teacher, int $studentId): LessonBalance
    {
        return $this->forTeacher($teacher, [$studentId])[$studentId];
    }

    /**
     * Élèves dont le paiement est à demander (cycle atteint ou dépassé).
     *
     * @param  array<int, LessonBalance>  $balances
     * @return list<int>
     */
    public static function toPay(array $balances): array
    {
        return array_keys(array_filter($balances, fn (LessonBalance $balance) => $balance->status()->needsAttention()));
    }
}
