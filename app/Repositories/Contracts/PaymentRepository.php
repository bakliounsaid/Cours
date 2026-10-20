<?php

namespace App\Repositories\Contracts;

use App\Models\Teacher;

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
}
