<?php

namespace App\Actions\Groups;

use App\Models\Group;
use App\Models\Student;

/**
 * Retire un élève d'un groupe. Sa fiche élève et son historique sont conservés.
 */
class RemoveStudentFromGroup
{
    public function handle(Group $group, Student $student): void
    {
        $group->students()->detach($student->id);
    }
}
