<?php

namespace App\Actions\Lessons;

use App\Enums\LessonStatus;
use App\Enums\LessonType;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\TimeSlot;
use App\Services\ConflictChecker;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Crée les séances des groupes à partir de leurs créneaux récurrents.
 * Idempotent : une séance déjà créée (même groupe, même début) n'est jamais dupliquée.
 * Une séance en conflit (salle ou enseignant déjà pris) est ignorée et signalée.
 */
class GenerateGroupLessons
{
    public function __construct(private readonly ConflictChecker $conflicts) {}

    /**
     * @param  Group|null  $only  limiter la génération à un seul groupe
     * @return array{created: int, skipped: list<array{group: string, starts_at: string, reason: string}>}
     */
    public function handle(CarbonInterface $from, CarbonInterface $to, ?Group $only = null): array
    {
        $report = ['created' => 0, 'skipped' => []];

        $groups = Group::query()
            ->active()
            ->when($only, fn (Builder $query) => $query->whereKey($only->id))
            ->whereDate('starts_on', '<=', $to)
            ->where(fn (Builder $query) => $query->whereNull('ends_on')->orWhereDate('ends_on', '>=', $from))
            ->with(['timeSlots' => fn ($query) => $query->active()])
            ->get();

        foreach (CarbonPeriod::create($from->copy()->startOfDay(), $to->copy()->startOfDay()) as $date) {
            foreach ($groups as $group) {
                if ($date->lt($group->starts_on) || ($group->ends_on && $date->gt($group->ends_on))) {
                    continue;
                }

                foreach ($group->timeSlots->where('day_of_week', $date->dayOfWeek) as $slot) {
                    $this->createOccurrence($group, $slot, $date, $report);
                }
            }
        }

        return $report;
    }

    /**
     * @param  array{created: int, skipped: list<array{group: string, starts_at: string, reason: string}>}  $report
     */
    private function createOccurrence(Group $group, TimeSlot $slot, CarbonInterface $date, array &$report): void
    {
        $start = $slot->startsOn($date);
        $end = $slot->endsOn($date);

        if (Lesson::query()->where('group_id', $group->id)->where('starts_at', $start)->exists()) {
            return;
        }

        $reason = match (true) {
            $this->conflicts->roomConflict($group->room_id, $start, $end) !== null => 'room',
            $this->conflicts->teacherConflict($group->teacher_id, $start, $end) !== null => 'teacher',
            default => null,
        };

        if ($reason === null) {
            try {
                DB::transaction(fn () => Lesson::query()->create([
                    'group_id' => $group->id,
                    'subject_id' => $group->subject_id,
                    'time_slot_id' => $slot->id,
                    'room_id' => $group->room_id,
                    'teacher_id' => $group->teacher_id,
                    'date' => $date->toDateString(),
                    'starts_at' => $start,
                    'ends_at' => $end,
                    'type' => LessonType::Group,
                    'status' => LessonStatus::Scheduled,
                ]));

                $report['created']++;

                return;
            } catch (QueryException $exception) {
                // Conflit détecté par la base au même moment (contrainte d'exclusion)
                if (! in_array($exception->getCode(), ['23P01', '23505'], true)) {
                    throw $exception;
                }
                $reason = 'conflict';
            }
        }

        $report['skipped'][] = ['group' => $group->name, 'starts_at' => $start->toDateTimeString(), 'reason' => $reason];
    }
}
