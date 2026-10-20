<?php

namespace App\Repositories\Contracts;

use App\Models\Teacher;
use Illuminate\Support\Collection;

/**
 * Lecture des enseignants.
 */
interface TeacherRepository
{
    /**
     * Enseignants actifs (sauf un) qui enseignent la matière, triés par nom.
     *
     * @return Collection<int, Teacher>
     */
    public function colleaguesTeaching(?int $subjectId, int $exceptTeacherId): Collection;

    /**
     * @return array<int, string> [id matière => nom]
     */
    public function subjectOptions(Teacher $teacher): array;

    /**
     * @return list<int>
     */
    public function subjectIds(Teacher $teacher): array;
}
