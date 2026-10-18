<?php

namespace App\Services;

use App\Enums\LessonStatus;
use App\Models\Lesson;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Anti-conflits : une salle, un enseignant ou un élève ne peut pas être
 * sur deux séances actives qui se chevauchent.
 * (La base de données le garantit aussi pour les salles et les enseignants.)
 */
class ConflictChecker
{
    public function roomConflict(int $roomId, CarbonInterface $start, CarbonInterface $end, ?int $ignoreLessonId = null): ?Lesson
    {
        return $this->overlapping($start, $end, $ignoreLessonId)->where('room_id', $roomId)->first();
    }

    public function teacherConflict(int $teacherId, CarbonInterface $start, CarbonInterface $end, ?int $ignoreLessonId = null): ?Lesson
    {
        return $this->overlapping($start, $end, $ignoreLessonId)->where('teacher_id', $teacherId)->first();
    }

    /**
     * Séances qui chevauchent ce créneau pour au moins un des élèves.
     *
     * @param  list<int>  $studentIds
     * @return Collection<int, Lesson>
     */
    public function studentConflicts(array $studentIds, CarbonInterface $start, CarbonInterface $end, ?int $ignoreLessonId = null): Collection
    {
        if ($studentIds === []) {
            return new Collection;
        }

        return $this->overlapping($start, $end, $ignoreLessonId)
            ->where(function (Builder $query) use ($studentIds) {
                $query->whereHas('group.students', fn (Builder $students) => $students->whereIn('students.id', $studentIds))
                    ->orWhereHas('students', fn (Builder $students) => $students->whereIn('students.id', $studentIds));
            })
            ->get();
    }

    /**
     * @return Builder<Lesson>
     */
    private function overlapping(CarbonInterface $start, CarbonInterface $end, ?int $ignoreLessonId): Builder
    {
        return Lesson::query()
            ->where('status', '!=', LessonStatus::Cancelled)
            ->where('starts_at', '<', $end)
            ->where('ends_at', '>', $start)
            ->when($ignoreLessonId, fn (Builder $query) => $query->whereKeyNot($ignoreLessonId));
    }
}
