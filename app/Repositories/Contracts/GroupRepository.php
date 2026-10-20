<?php

namespace App\Repositories\Contracts;

use App\Models\Group;
use App\Models\Teacher;
use Illuminate\Support\Collection;

/**
 * Lecture des groupes.
 */
interface GroupRepository
{
    /**
     * Groupes d'un enseignant (actifs ou archivés), avec matière, niveau, salle, créneaux et nombre d'élèves.
     *
     * @return Collection<int, Group>
     */
    public function forTeacher(Teacher $teacher, bool $active = true, string $search = ''): Collection;

    public function countForTeacher(Teacher $teacher, bool $active = true): int;

    /**
     * @return array<int, string> [id => nom]
     */
    public function optionsForTeacher(Teacher $teacher, bool $onlyActive = true): array;

    /** Groupe de l'enseignant, sinon 404. */
    public function findForTeacher(Teacher $teacher, int|string $id): Group;
}
