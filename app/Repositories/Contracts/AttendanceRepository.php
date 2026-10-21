<?php

namespace App\Repositories\Contracts;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Collection;

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
     * Comme countByStudentForTeacher, mais pour tous les enseignants à la fois.
     *
     * @param  list<int>  $studentIds
     * @param  list<AttendanceStatus>  $statuses
     * @return array<int, array<int, int>> [id élève => [id enseignant => nombre]]
     */
    public function countByStudentAndTeacher(array $studentIds, array $statuses): array;

    /**
     * Nombre de présences, retards et absences d'un élève (hors séances annulées).
     *
     * @return array{present: int, late: int, absent: int}
     */
    public function statsForStudent(Student $student): array;

    /**
     * Dernières présences d'un élève, avec la séance (matière, enseignant, groupe).
     *
     * @return Collection<int, Attendance>
     */
    public function recentForStudent(Student $student, int $limit = 15): Collection;

    /**
     * Présences déjà saisies pour une séance.
     *
     * @return array<int, AttendanceStatus> [id élève => statut]
     */
    public function statusesForLesson(Lesson $lesson): array;
}
