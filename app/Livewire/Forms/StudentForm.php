<?php

namespace App\Livewire\Forms;

use App\Actions\Students\SaveStudent;
use App\Livewire\Forms\Concerns\PrefixesActionErrors;
use App\Models\Student;
use App\Models\Teacher;
use App\Repositories\Contracts\GroupRepository;
use App\Repositories\Contracts\StudentRepository;
use App\Rules\AlgerianPhone;
use App\Support\Phone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

/**
 * Élève saisi par un enseignant. À la création, l'élève rejoint un de ses groupes
 * et/ou ses cours individuels (sinon il n'apparaîtrait pas dans « Mes élèves »).
 */
class StudentForm extends ModelForm
{
    use PrefixesActionErrors;

    public string $first_name = '';

    public string $last_name = '';

    public string $level_id = '';

    public string $parent_phone = '';

    public string $notes = '';

    /** Groupe à rejoindre (création uniquement). */
    public string $group_id = '';

    /** Suivi en cours individuels (création uniquement). */
    public bool $individual = false;

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'level_id' => ['required', Rule::exists('levels', 'id')],
            'parent_phone' => ['required', 'string', new AlgerianPhone],
            'notes' => ['nullable', 'string', 'max:1000'],
            'group_id' => ['nullable', Rule::in(array_map('strval', array_keys(app(GroupRepository::class)->optionsForTeacher($this->teacher()))))],
            'individual' => ['boolean'],
        ];
    }

    /**
     * @param  Student  $model
     */
    public function load(Model $model): void
    {
        $this->editingId = $model->id;
        $this->first_name = $model->first_name;
        $this->last_name = $model->last_name;
        $this->level_id = (string) $model->level_id;
        $this->parent_phone = Phone::format($model->parent_phone);
        $this->notes = (string) $model->notes;
        $this->group_id = '';
        $this->individual = false;
    }

    public function save(): Student
    {
        $data = $this->validate();
        $teacher = $this->teacher();
        $students = app(StudentRepository::class);

        return $this->withFormErrors(fn () => app(SaveStudent::class)->handle(
            $teacher,
            $data,
            student: $this->editingId ? $students->findForTeacher($teacher, $this->editingId) : null,
            group: ! $this->isEditing() && $data['group_id'] !== '' ? app(GroupRepository::class)->findForTeacher($teacher, $data['group_id']) : null,
            individual: ! $this->isEditing() && $data['individual'],
        ));
    }

    private function teacher(): Teacher
    {
        return auth()->user()->teacher;
    }
}
