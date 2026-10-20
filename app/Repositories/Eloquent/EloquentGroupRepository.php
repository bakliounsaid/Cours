<?php

namespace App\Repositories\Eloquent;

use App\Models\Group;
use App\Models\Teacher;
use App\Repositories\Contracts\GroupRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EloquentGroupRepository implements GroupRepository
{
    public function forTeacher(Teacher $teacher, bool $active = true, string $search = ''): Collection
    {
        $search = trim($search);

        return Group::query()
            ->where('teacher_id', $teacher->id)
            ->where('is_active', $active)
            ->with(['subject', 'level', 'room', 'timeSlots' => fn ($query) => $query->ordered()])
            ->withCount(['students' => fn (Builder $query) => $query->where('is_active', true)])
            ->when($search !== '', fn (Builder $query) => $query->whereLike('name', "%{$search}%"))
            ->orderBy('name')
            ->get();
    }

    public function countForTeacher(Teacher $teacher, bool $active = true): int
    {
        return Group::query()->where('teacher_id', $teacher->id)->where('is_active', $active)->count();
    }

    public function optionsForTeacher(Teacher $teacher, bool $onlyActive = true): array
    {
        return Group::query()
            ->where('teacher_id', $teacher->id)
            ->when($onlyActive, fn (Builder $query) => $query->active())
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    public function findForTeacher(Teacher $teacher, int|string $id): Group
    {
        return Group::query()->where('teacher_id', $teacher->id)->with('timeSlots')->findOrFail($id);
    }
}
