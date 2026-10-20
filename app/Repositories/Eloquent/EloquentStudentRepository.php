<?php

namespace App\Repositories\Eloquent;

use App\Models\Group;
use App\Models\Student;
use App\Models\Teacher;
use App\Repositories\Contracts\StudentRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EloquentStudentRepository implements StudentRepository
{
    public function activeIdsForTeacher(Teacher $teacher): array
    {
        return $this->ofTeacher($teacher)->active()->pluck('students.id')->map(fn ($id) => (int) $id)->all();
    }

    public function paginateForTeacher(Teacher $teacher, string $mode = self::ALL, string $search = '', ?array $onlyIds = null, int $perPage = 20): LengthAwarePaginator
    {
        $search = trim($search);

        return $this->ofTeacher($teacher)
            ->active()
            ->with(['level', 'groups' => fn ($query) => $query->where('teacher_id', $teacher->id)->where('is_active', true)])
            ->withExists(['individualTeachers as is_individual' => fn (Builder $query) => $query->whereKey($teacher->id)])
            ->when($search !== '', fn (Builder $query) => $query->search($search))
            ->when($mode === self::IN_GROUP, fn (Builder $query) => $this->inGroupsOf($query, $teacher))
            ->when($mode === self::INDIVIDUAL, fn (Builder $query) => $this->individualOf($query, $teacher))
            ->when($onlyIds !== null, fn (Builder $query) => $query->whereKey($onlyIds))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate($perPage);
    }

    public function countsForTeacher(Teacher $teacher): array
    {
        return [
            'all' => $this->ofTeacher($teacher)->active()->count(),
            'group' => $this->inGroupsOf($this->ofTeacher($teacher)->active(), $teacher)->count(),
            'individual' => $this->individualOf(Student::query()->active(), $teacher)->count(),
        ];
    }

    public function findForTeacher(Teacher $teacher, int|string $id): Student
    {
        return $this->ofTeacher($teacher)->findOrFail($id);
    }

    public function rosterOfGroup(Group $group): Collection
    {
        return $group->students()->active()->with('level')->orderBy('last_name')->orderBy('first_name')->get();
    }

    public function candidatesForGroup(Group $group, string $search, int $limit = 8): Collection
    {
        return Student::query()
            ->active()
            ->search($search)
            ->whereDoesntHave('groups', fn (Builder $query) => $query->whereKey($group->id))
            ->with('level')
            // Les élèves du niveau du groupe d'abord
            ->orderByRaw('case when level_id = ? then 0 else 1 end', [$group->level_id])
            ->orderBy('last_name')
            ->limit($limit)
            ->get();
    }

    public function findActive(int $id): Student
    {
        return Student::query()->active()->findOrFail($id);
    }

    public function findInGroup(Group $group, int|string $id): Student
    {
        return $group->students()->findOrFail($id);
    }

    /**
     * Élèves d'un enseignant : ceux de ses groupes et ceux qu'il suit en cours individuels.
     *
     * @return Builder<Student>
     */
    private function ofTeacher(Teacher $teacher): Builder
    {
        return Student::query()->where(function (Builder $query) use ($teacher) {
            $this->inGroupsOf($query, $teacher)->orWhereHas('individualTeachers', fn (Builder $teachers) => $teachers->whereKey($teacher->id));
        });
    }

    /**
     * @param  Builder<Student>  $query
     * @return Builder<Student>
     */
    private function inGroupsOf(Builder $query, Teacher $teacher): Builder
    {
        return $query->whereHas('groups', fn (Builder $groups) => $groups->where('teacher_id', $teacher->id));
    }

    /**
     * @param  Builder<Student>  $query
     * @return Builder<Student>
     */
    private function individualOf(Builder $query, Teacher $teacher): Builder
    {
        return $query->whereHas('individualTeachers', fn (Builder $teachers) => $teachers->whereKey($teacher->id));
    }
}
