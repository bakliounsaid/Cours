<?php

namespace App\Repositories\Contracts;

use App\Enums\AttendanceStatus;
use App\Models\Lesson;
use App\Models\Teacher;

/**
 * Lecture des présences.
 */
interface AttendanceRepository
{
    /**
     * Nombre de séances de chaque élève chez l'enseignant, pour les statuts donnés (hors séances annulées).
     *
     * @param  list<int>  $studentIds
     * @param  list<AttendanceStatus>  $statuses
     * @return array<int, int> [id élève => nombre]
     */
    public function countByStudentForTeacher(Teacher $teacher, array $studentIds, array $statuses): array;

    /**
     * Présences déjà saisies pour une séance.
     *
     * @return array<int, AttendanceStatus> [id élève => statut]
     */
    public function statusesForLesson(Lesson $lesson): array;
}
