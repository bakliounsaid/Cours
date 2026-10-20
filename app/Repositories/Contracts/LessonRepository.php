<?php

namespace App\Repositories\Contracts;

use App\Models\Group;
use App\Models\Lesson;
use App\Models\Teacher;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Lecture des séances.
 */
interface LessonRepository
{
    /** Onglets de la liste des séances d'un enseignant. */
    public const UPCOMING = 'upcoming';

    public const PAST = 'past';

    public const CANCELLED = 'cancelled';

    /**
     * Séances d'un enseignant sur une période, dans l'ordre (relations d'affichage chargées).
     *
     * @return Collection<int, Lesson>
     */
    public function forTeacherBetween(Teacher $teacher, CarbonInterface $from, CarbonInterface $to, bool $withCancelled = false): Collection;

    /**
     * @return LengthAwarePaginator<int, Lesson>
     */
    public function paginateForTeacher(Teacher $teacher, string $tab, ?int $groupId = null, int $perPage = 20): LengthAwarePaginator;

    public function countForTeacher(Teacher $teacher, string $tab, ?int $groupId = null): int;

    /** Séances faites (appel validé) sur une période. */
    public function doneCountForTeacher(Teacher $teacher, CarbonInterface $from, CarbonInterface $to): int;

    /** Montant gagné (pointages) sur une période. */
    public function earnedByTeacher(Teacher $teacher, CarbonInterface $from, CarbonInterface $to): int;

    /**
     * @return Collection<int, Lesson>
     */
    public function upcomingForGroup(Group $group, int $limit = 3): Collection;

    /**
     * Séances passées d'un groupe avec le nombre de présences (attendances_count, present_count).
     *
     * @return Collection<int, Lesson>
     */
    public function pastForGroup(Group $group, int $limit = 10): Collection;

    /**
     * Prochain cours individuel de chaque élève chez l'enseignant.
     *
     * @param  iterable<int>  $studentIds
     * @return Collection<int, Lesson> [id élève => séance]
     */
    public function nextIndividualByStudent(Teacher $teacher, iterable $studentIds): Collection;
}
