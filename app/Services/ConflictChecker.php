<?php

namespace App\Services;

use App\Enums\LessonStatus;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\TimeSlot;
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
     * Créneau hebdomadaire déjà pris par un autre groupe actif dans la même salle
     * ou avec le même enseignant.
     *
     * @param  list<int>  $timeSlotIds
     * @return array{slot: TimeSlot, group: Group, reason: 'room'|'teacher'}|null
     */
    public function groupSlotConflict(array $timeSlotIds, int $roomId, int $teacherId, ?int $ignoreGroupId = null): ?array
    {
        if ($timeSlotIds === []) {
            return null;
        }

        $slots = TimeSlot::query()->whereKey($timeSlotIds)->get();

        $group = Group::query()
            ->active()
            ->when($ignoreGroupId, fn (Builder $query) => $query->whereKeyNot($ignoreGroupId))
            ->where(fn (Builder $query) => $query->where('room_id', $roomId)->orWhere('teacher_id', $teacherId))
            ->whereHas('timeSlots', fn (Builder $query) => $query->where(function (Builder $query) use ($slots) {
                // Chevauchement le même jour, pas seulement le même créneau
                foreach ($slots as $slot) {
                    $query->orWhere(fn (Builder $query) => $query
                        ->where('day_of_week', $slot->day_of_week)
                        ->where('starts_at', '<', $slot->ends_at)
                        ->where('ends_at', '>', $slot->starts_at));
                }
            }))
            ->with('timeSlots')
            ->first();

        if (! $group) {
            return null;
        }

        $slot = $slots->first(fn (TimeSlot $slot) => $group->timeSlots->contains(fn (TimeSlot $other) => $other->day_of_week === $slot->day_of_week
            && $other->starts_at < $slot->ends_at
            && $other->ends_at > $slot->starts_at));

        return ['slot' => $slot, 'group' => $group, 'reason' => $group->room_id === $roomId ? 'room' : 'teacher'];
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
