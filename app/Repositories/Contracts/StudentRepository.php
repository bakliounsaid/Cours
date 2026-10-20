<?php

namespace App\Repositories\Contracts;

use App\Models\Group;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Lecture des élèves.
 */
interface StudentRepository
{
    /** Filtres de la liste « Mes élèves ». */
    public const ALL = 'all';

    public const IN_GROUP = 'group';

    public const INDIVIDUAL = 'individual';

    /**
     * Identifiants des élèves actifs d'un enseignant (groupes et cours individuels).
     *
     * @return list<int>
     */
    public function activeIdsForTeacher(Teacher $teacher): array;

    /**
     * @param  list<int>|null  $onlyIds  restreindre à ces élèves (ex. paiement à régler)
     * @return LengthAwarePaginator<int, Student>
     */
    public function paginateForTeacher(Teacher $teacher, string $mode = self::ALL, string $search = '', ?array $onlyIds = null, int $perPage = 20): LengthAwarePaginator;

    /**
     * @return array{all: int, group: int, individual: int}
     */
    public function countsForTeacher(Teacher $teacher): array;

    /** Élève de l'enseignant, sinon 404. */
    public function findForTeacher(Teacher $teacher, int|string $id): Student;

    /**
     * Élèves actifs d'un groupe, avec leur niveau.
     *
     * @return Collection<int, Student>
     */
    public function rosterOfGroup(Group $group): Collection;

    /**
     * Élèves actifs de l'école pouvant rejoindre le groupe (ceux du même niveau d'abord).
     *
     * @return Collection<int, Student>
     */
    public function candidatesForGroup(Group $group, string $search, int $limit = 8): Collection;

    public function findActive(int $id): Student;

    /** Élève inscrit dans ce groupe, sinon 404. */
    public function findInGroup(Group $group, int|string $id): Student;
}
