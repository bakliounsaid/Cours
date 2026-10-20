<?php

namespace App\Actions\Students;

use App\Actions\Groups\AddStudentToGroup;
use App\Enums\Locale;
use App\Models\Group;
use App\Models\Student;
use App\Models\Teacher;
use App\Support\Phone;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Crée ou modifie un élève saisi par un enseignant. À la création, l'élève rejoint
 * un groupe de l'enseignant et/ou ses cours individuels (sinon il n'apparaîtrait nulle part).
 */
class SaveStudent
{
    public function __construct(
        private readonly AddStudentToGroup $addToGroup,
        private readonly FollowIndividually $followIndividually,
    ) {}

    /**
     * @param  array{first_name: string, last_name: string, level_id: int|string, parent_phone: string, notes?: ?string}  $data
     *
     * @throws ValidationException groupe complet, ou ni groupe ni cours individuel à la création
     */
    public function handle(Teacher $teacher, array $data, ?Student $student = null, ?Group $group = null, bool $individual = false): Student
    {
        $isNew = $student === null;

        if ($isNew && ! $group && ! $individual) {
            throw ValidationException::withMessages(['group_id' => __('teacher.students.assign_required')]);
        }

        return DB::transaction(function () use ($teacher, $data, $student, $group, $individual, $isNew) {
            $student ??= new Student(['enrolled_at' => today()->toDateString(), 'is_active' => true, 'parent_locale' => Locale::default()]);

            $student->fill([
                'first_name' => trim($data['first_name']),
                'last_name' => trim($data['last_name']),
                'level_id' => (int) $data['level_id'],
                'parent_phone' => Phone::normalize($data['parent_phone']),
                'notes' => filled($data['notes'] ?? null) ? trim($data['notes']) : null,
            ])->save();

            if ($isNew && $group) {
                $this->addToGroup->handle($group, $student);
            }

            if ($isNew && $individual) {
                $this->followIndividually->start($teacher, $student);
            }

            return $student;
        });
    }
}
