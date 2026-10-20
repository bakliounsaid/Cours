<?php

namespace App\Actions\Lessons;

use App\Enums\LessonEventType;
use App\Enums\LessonStatus;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Annulation d'une séance par l'enseignant :
 * - « once » : seulement cette séance (le créneau reste les autres semaines) ;
 * - « permanent » : le créneau est retiré du groupe, cette séance et les suivantes
 *   sur ce créneau sont annulées (elles restent visibles, avec le motif).
 * Chaque séance annulée reçoit une entrée dans son journal.
 */
class CancelLesson
{
    public const ONCE = 'once';

    public const PERMANENT = 'permanent';

    /**
     * @return int nombre de séances annulées
     *
     * @throws ValidationException
     */
    public function handle(Lesson $lesson, string $scope, ?string $reason, User $by): int
    {
        if (! $lesson->canBeCancelled()) {
            throw ValidationException::withMessages(['scope' => __('teacher.cancel.not_allowed')]);
        }

        if ($scope === self::PERMANENT && ! $lesson->isRecurring()) {
            throw ValidationException::withMessages(['scope' => __('teacher.cancel.not_recurring')]);
        }

        $reason = filled($reason) ? trim($reason) : null;

        return DB::transaction(function () use ($lesson, $scope, $reason, $by) {
            if ($scope === self::ONCE) {
                $this->cancel($lesson, ['scope' => self::ONCE, 'reason' => $reason], $by);

                return 1;
            }

            $slot = $lesson->timeSlot;
            $lesson->group->timeSlots()->detach($slot->id);

            // Cette séance et toutes les suivantes du groupe sur ce créneau
            $lessons = $lesson->group->lessons()
                ->where('time_slot_id', $slot->id)
                ->where('status', LessonStatus::Scheduled)
                ->where('starts_at', '>=', $lesson->starts_at)
                ->whereDoesntHave('checkin')
                ->get();

            foreach ($lessons as $occurrence) {
                $this->cancel($occurrence, ['scope' => self::PERMANENT, 'reason' => $reason, 'slot' => $slot->label()], $by);
            }

            return $lessons->count();
        });
    }

    /**
     * @param  array{scope: string, reason: ?string, slot?: string}  $data
     */
    private function cancel(Lesson $lesson, array $data, User $by): void
    {
        $lesson->update(['status' => LessonStatus::Cancelled, 'cancel_reason' => $data['reason']]);
        $lesson->events()->create(['user_id' => $by->id, 'type' => LessonEventType::Cancelled, 'data' => $data]);
    }
}
