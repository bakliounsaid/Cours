<?php

namespace App\Actions\Groups;

use App\Models\Group;
use App\Models\Student;
use Illuminate\Validation\ValidationException;

/**
 * Inscrit un élève dans un groupe, dans la limite des places disponibles.
 */
class AddStudentToGroup
{
    /**
     * @throws ValidationException si le groupe est complet
     */
    public function handle(Group $group, Student $student): void
    {
        if ($group->students()->whereKey($student->id)->exists()) {
            return;
        }

        if ($group->isFull()) {
            throw ValidationException::withMessages([
                'group_id' => __('teacher.groups.full', ['name' => $group->name, 'count' => $group->capacity()]),
            ]);
        }

        $group->students()->attach($student->id, ['joined_at' => today()->toDateString()]);
        $student->subjects()->syncWithoutDetaching([$group->subject_id]);
    }
}
