<?php

namespace App\Actions\Groups;

use App\Actions\Lessons\GenerateGroupLessons;
use App\Enums\LessonStatus;
use App\Enums\LessonType;
use App\Models\Group;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;

/**
 * Crée ou modifie un groupe d'un enseignant.
 * Si l'emploi du temps change (salle, créneaux, dates, archivage), les séances
 * à venir non modifiées sont recréées pour rester alignées sur le groupe.
 */
class SaveGroup
{
    public function __construct(private readonly GenerateGroupLessons $generator) {}

    /**
     * @param  array{name: string, subject_id: int|string, level_id: int|string, room_id: int|string, time_slot_ids: list<int|string>, starts_on: string, is_active: bool}  $data
     */
    public function handle(Teacher $teacher, array $data, ?Group $group = null): Group
    {
        return DB::transaction(function () use ($teacher, $data, $group) {
            $group ??= new Group;

            $group->fill([
                'name' => trim($data['name']),
                'subject_id' => (int) $data['subject_id'],
                'level_id' => (int) $data['level_id'],
                'room_id' => (int) $data['room_id'],
                'starts_on' => $data['starts_on'],
                'is_active' => (bool) $data['is_active'],
            ]);
            $group->teacher_id = $teacher->id;

            $isNew = ! $group->exists;
            $group->save();

            $changes = $group->timeSlots()->sync(array_map('intval', $data['time_slot_ids']));
            $slotsChanged = $changes['attached'] !== [] || $changes['detached'] !== [];

            if ($isNew || $slotsChanged || $group->wasChanged(['room_id', 'subject_id', 'starts_on', 'is_active'])) {
                $this->refreshUpcomingLessons($group);
            }

            return $group->load('timeSlots');
        });
    }

    /** Archive (ou réactive) un groupe : ses séances à venir suivent. */
    public function setActive(Group $group, bool $active): void
    {
        DB::transaction(function () use ($group, $active) {
            $group->update(['is_active' => $active]);
            $this->refreshUpcomingLessons($group);
        });
    }

    /**
     * Supprime les séances de groupe à venir encore « prévues » et sans changement
     * manuel (remplacement…), puis les régénère si le groupe est actif.
     */
    private function refreshUpcomingLessons(Group $group): void
    {
        $group->lessons()
            ->where('type', LessonType::Group)
            ->where('status', LessonStatus::Scheduled)
            ->whereNull('original_teacher_id')
            ->where('starts_at', '>', now())
            ->whereDoesntHave('attendances')
            ->delete();

        if ($group->is_active) {
            $this->generator->handle(today(), today()->addDays((int) config('educoeur.lessons.generate_days_ahead')), $group);
        }
    }
}
