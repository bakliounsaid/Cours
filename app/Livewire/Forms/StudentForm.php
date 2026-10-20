<?php

namespace App\Livewire\Forms;

use App\Actions\Groups\AddStudentToGroup;
use App\Enums\Locale;
use App\Livewire\Forms\Concerns\PrefixesActionErrors;
use App\Models\Student;
use App\Models\Teacher;
use App\Rules\AlgerianPhone;
use App\Support\Phone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
            'group_id' => ['nullable', Rule::in($this->teacher()->groups()->active()->pluck('id')->map(fn ($id) => (string) $id)->all())],
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

        if (! $this->isEditing() && $data['group_id'] === '' && ! $data['individual']) {
            throw ValidationException::withMessages([
                $this->getPropertyName().'.group_id' => __('teacher.students.assign_required'),
            ]);
        }

        return $this->withFormErrors(fn () => DB::transaction(function () use ($data) {
            $student = $this->editingId
                ? $this->teacher()->students()->findOrFail($this->editingId)
                : new Student(['enrolled_at' => today()->toDateString(), 'is_active' => true, 'parent_locale' => Locale::default()]);

            $student->fill([
                'first_name' => trim($data['first_name']),
                'last_name' => trim($data['last_name']),
                'level_id' => (int) $data['level_id'],
                'parent_phone' => Phone::normalize($data['parent_phone']),
                'notes' => filled($data['notes']) ? trim($data['notes']) : null,
            ])->save();

            if (! $this->isEditing()) {
                if ($data['group_id'] !== '') {
                    app(AddStudentToGroup::class)->handle($this->teacher()->groups()->findOrFail($data['group_id']), $student);
                }

                if ($data['individual']) {
                    $this->teacher()->individualStudents()->syncWithoutDetaching([$student->id]);
                }
            }

            return $student;
        }));
    }

    private function teacher(): Teacher
    {
        return auth()->user()->teacher;
    }
}
