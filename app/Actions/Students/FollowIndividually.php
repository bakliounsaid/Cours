<?php

namespace App\Actions\Students;

use App\Models\Student;
use App\Models\Teacher;

/**
 * Suivi d'un élève en cours individuels par un enseignant (début ou fin).
 */
class FollowIndividually
{
    public function start(Teacher $teacher, Student $student): void
    {
        $teacher->individualStudents()->syncWithoutDetaching([$student->id]);
    }

    /** Les séances déjà programmées restent au planning. */
    public function stop(Teacher $teacher, Student $student): void
    {
        $teacher->individualStudents()->detach($student->id);
    }
}
