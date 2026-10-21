<?php

namespace App\Repositories\Contracts;

use App\Models\Payment;
use App\Models\Student;
use App\Models\Teacher;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Lecture des paiements.
 */
interface PaymentRepository
{
    /**
     * Nombre de séances payées par chaque élève à l'enseignant.
     *
     * @param  list<int>  $studentIds
     * @return array<int, int> [id élève => séances couvertes]
     */
    public function lessonsCoveredByStudent(Teacher $teacher, array $studentIds): array;

    /**
     * Comme lessonsCoveredByStudent, mais pour tous les enseignants à la fois.
     *
     * @param  list<int>  $studentIds
     * @return array<int, array<int, int>> [id élève => [id enseignant => séances couvertes]]
     */
    public function coveredByStudentAndTeacher(array $studentIds): array;

    /**
     * Paiements d'un élève, du plus récent au plus ancien (enseignant et auteur de la saisie).
     *
     * @return Collection<int, Payment>
     */
    public function forStudent(Student $student): Collection;

    public function totalPaidByStudent(Student $student): int;

    /** Montant total encaissé sur une période. */
    public function totalBetween(CarbonInterface $from, CarbonInterface $to): int;
}
