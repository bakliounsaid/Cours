<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Enums\PaymentStatus;
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

        $attended = $this->attendances->countByStudentForTeacher($teacher, $ids, $this->countedStatuses());
        $covered = $this->payments->lessonsCoveredByStudent($teacher, $ids);
        $cycle = max(1, $this->settings->lessonsPerCycle());

        return collect($ids)->mapWithKeys(fn (int $id) => [$id => new LessonBalance(
            attended: $attended[$id] ?? 0,
            covered: $covered[$id] ?? 0,
            cycle: $cycle,
            preReminder: $this->settings->preReminderEnabled(),
        )])->all();
    }

    /**
     * Compteurs de plusieurs élèves chez tous leurs enseignants (ceux où ils ont des séances ou des paiements).
     *
     * @param  iterable<int>  $studentIds
     * @return array<int, array<int, LessonBalance>> [id élève => [id enseignant => compteur]]
     */
    public function forStudentsAllTeachers(iterable $studentIds): array
    {
        $ids = collect($studentIds)->map(fn ($id) => (int) $id)->unique()->values()->all();

        if ($ids === []) {
            return [];
        }

        $attended = $this->attendances->countByStudentAndTeacher($ids, $this->countedStatuses());
        $covered = $this->payments->coveredByStudentAndTeacher($ids);
        $cycle = max(1, $this->settings->lessonsPerCycle());

        $result = [];

        foreach ($ids as $studentId) {
            $teacherIds = array_unique([...array_keys($attended[$studentId] ?? []), ...array_keys($covered[$studentId] ?? [])]);

            foreach ($teacherIds as $teacherId) {
                $result[$studentId][$teacherId] = new LessonBalance(
                    attended: $attended[$studentId][$teacherId] ?? 0,
                    covered: $covered[$studentId][$teacherId] ?? 0,
                    cycle: $cycle,
                    preReminder: $this->settings->preReminderEnabled(),
                );
            }
        }

        return $result;
    }

    /**
     * Statut le plus urgent parmi plusieurs compteurs (en retard > à régler > bientôt > à jour).
     *
     * @param  array<int, LessonBalance>  $balances
     */
    public static function worstStatus(array $balances): PaymentStatus
    {
        $order = [PaymentStatus::UpToDate, PaymentStatus::ReminderSoon, PaymentStatus::Due, PaymentStatus::Overdue];
        $worst = PaymentStatus::UpToDate;

        foreach ($balances as $balance) {
            if (array_search($balance->status(), $order, true) > array_search($worst, $order, true)) {
                $worst = $balance->status();
            }
        }

        return $worst;
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

    /**
     * Statuts qui consomment une séance : présent et en retard (+ absent selon le réglage).
     *
     * @return list<AttendanceStatus>
     */
    private function countedStatuses(): array
    {
        return array_values(array_filter(
            AttendanceStatus::cases(),
            fn (AttendanceStatus $status) => $status->isPresence() || $this->settings->countsAbsences(),
        ));
    }
}
