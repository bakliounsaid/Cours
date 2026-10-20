<?php

namespace App\Repositories\Eloquent;

use App\Models\Teacher;
use App\Repositories\Contracts\TeacherRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EloquentTeacherRepository implements TeacherRepository
{
    public function colleaguesTeaching(?int $subjectId, int $exceptTeacherId): Collection
    {
        return Teacher::query()
            ->active()
            ->whereKeyNot($exceptTeacherId)
            ->when($subjectId, fn (Builder $query) => $query->whereHas('subjects', fn (Builder $subjects) => $subjects->whereKey($subjectId)))
            ->with('user')
            ->get()
            ->sortBy('name')
            ->values();
    }

    public function subjectOptions(Teacher $teacher): array
    {
        return $teacher->subjects()->ordered()->get()->pluck('name', 'id')->all();
    }

    public function subjectIds(Teacher $teacher): array
    {
        return $teacher->subjects()->pluck('subjects.id')->map(fn ($id) => (int) $id)->all();
    }
}
