<?php

namespace App\Repositories\Eloquent;

use App\Enums\AttendanceStatus;
use App\Enums\LessonStatus;
use App\Enums\LessonType;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\Teacher;
use App\Repositories\Contracts\LessonRepository;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EloquentLessonRepository implements LessonRepository
{
    public function forTeacherBetween(Teacher $teacher, CarbonInterface $from, CarbonInterface $to, bool $withCancelled = false): Collection
    {
        return Lesson::query()
            ->where('teacher_id', $teacher->id)
            ->with(['group.students' => fn ($query) => $query->active(), 'subject', 'room', 'students', 'checkin'])
            ->whereBetween('starts_at', [$from, $to])
            ->when(! $withCancelled, fn (Builder $query) => $query->notCancelled())
            ->chronological()
            ->get();
    }

    public function paginateForTeacher(Teacher $teacher, string $tab, ?int $groupId = null, int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->tabQuery($teacher, $tab, $groupId)
            ->with(['group', 'subject', 'room', 'students', 'checkin'])
            ->withCount([
                'attendances',
                'attendances as present_count' => fn (Builder $query) => $query->whereIn('status', [AttendanceStatus::Present, AttendanceStatus::Late]),
            ]);

        $tab === self::UPCOMING
            ? $query->orderBy('starts_at')->orderBy('id')
            : $query->orderByDesc('starts_at')->orderByDesc('id');

        return $query->paginate($perPage);
    }

    public function countForTeacher(Teacher $teacher, string $tab, ?int $groupId = null): int
    {
        return $this->tabQuery($teacher, $tab, $groupId)->count();
    }

    public function doneCountForTeacher(Teacher $teacher, CarbonInterface $from, CarbonInterface $to): int
    {
        return Lesson::query()
            ->where('teacher_id', $teacher->id)
            ->where('status', LessonStatus::Done)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->count();
    }

    public function earnedByTeacher(Teacher $teacher, CarbonInterface $from, CarbonInterface $to): int
    {
        return (int) $teacher->checkins()->whereBetween('checked_at', [$from, $to])->sum('rate_amount');
    }

    public function upcomingForGroup(Group $group, int $limit = 3): Collection
    {
        return $group->lessons()
            ->with('room')
            ->where('starts_at', '>=', now())
            ->notCancelled()
            ->chronological()
            ->limit($limit)
            ->get();
    }

    public function pastForGroup(Group $group, int $limit = 10): Collection
    {
        return $group->lessons()
            ->with('checkin')
            ->withCount([
                'attendances',
                'attendances as present_count' => fn (Builder $query) => $query->whereIn('status', [AttendanceStatus::Present, AttendanceStatus::Late]),
            ])
            ->where('starts_at', '<', now())
            ->orderByDesc('starts_at')
            ->limit($limit)
            ->get();
    }

    public function nextIndividualByStudent(Teacher $teacher, iterable $studentIds): Collection
    {
        $ids = collect($studentIds)->all();

        if ($ids === []) {
            return collect();
        }

        return Lesson::query()
            ->where('teacher_id', $teacher->id)
            ->where('type', LessonType::Individual)
            ->where('status', LessonStatus::Scheduled)
            ->where('starts_at', '>=', now())
            ->whereHas('students', fn (Builder $query) => $query->whereIn('students.id', $ids))
            ->with('students:id')
            ->chronological()
            ->get()
            ->flatMap(fn (Lesson $lesson) => $lesson->students->map(fn (Student $student) => [$student->id, $lesson]))
            ->unique(0)
            ->mapWithKeys(fn (array $pair) => [$pair[0] => $pair[1]]);
    }

    /**
     * @return Builder<Lesson>
     */
    private function tabQuery(Teacher $teacher, string $tab, ?int $groupId): Builder
    {
        return Lesson::query()
            ->where('teacher_id', $teacher->id)
            ->when($groupId, fn (Builder $query) => $query->where('group_id', $groupId))
            ->when($tab === self::UPCOMING, fn (Builder $query) => $query->notCancelled()->where('ends_at', '>=', now()))
            ->when($tab === self::PAST, fn (Builder $query) => $query->notCancelled()->where('ends_at', '<', now()))
            ->when($tab === self::CANCELLED, fn (Builder $query) => $query->where('status', LessonStatus::Cancelled));
    }
}
