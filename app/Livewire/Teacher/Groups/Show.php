<?php

namespace App\Livewire\Teacher\Groups;

use App\Actions\Groups\AddStudentToGroup;
use App\Enums\LessonStatus;
use App\Livewire\Concerns\WithModalForm;
use App\Livewire\Forms\StudentForm;
use App\Livewire\Teacher\Concerns\ForCurrentTeacher;
use App\Models\Group;
use App\Models\Student;
use App\Support\Catalog;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Fiche d'un groupe : liste des élèves, ajout (existant ou nouveau) et retrait.
 */
#[Layout('components.layouts.teacher')]
class Show extends Component
{
    use ForCurrentTeacher;
    use WithModalForm {
        create as openStudentForm;
    }

    #[Locked]
    public Group $group;

    public StudentForm $form;

    public string $studentSearch = '';

    public function mount(Group $group): void
    {
        abort_unless($group->teacher_id === $this->teacher->id, 404);

        $this->group = $group;
    }

    /** Nouvel élève, inscrit directement dans ce groupe. */
    public function create(): void
    {
        $this->openStudentForm();
        $this->form->group_id = (string) $this->group->id;
        $this->form->level_id = (string) $this->group->level_id;
    }

    public function addStudent(int $studentId): void
    {
        $student = Student::query()->active()->findOrFail($studentId);

        try {
            app(AddStudentToGroup::class)->handle($this->group, $student);
        } catch (ValidationException $exception) {
            $this->dispatch('toast', type: 'error', message: collect($exception->errors())->flatten()->first());

            return;
        }

        $this->studentSearch = '';
        $this->dispatch('toast', type: 'success', message: __('teacher.groups.student_added', ['name' => $student->full_name]));
    }

    public function removeStudent(int $studentId): void
    {
        $student = $this->group->students()->findOrFail($studentId);
        $this->group->students()->detach($student->id);

        $this->dispatch('toast', type: 'success', message: __('teacher.groups.student_removed', ['name' => $student->full_name]));
    }

    /** Un enseignant ne supprime pas un élève : il le retire du groupe. */
    public function delete(int $id): void
    {
        $this->removeStudent($id);
    }

    protected function findModel(int $id): Student
    {
        return $this->group->students()->findOrFail($id);
    }

    protected function performDelete(Model $model): void
    {
        abort(403);
    }

    public function render(): View
    {
        $this->group->load(['subject', 'level', 'room', 'timeSlots' => fn ($query) => $query->ordered()]);

        $students = $this->group->students()->active()->with('level')->orderBy('last_name')->orderBy('first_name')->get();
        $term = trim($this->studentSearch);

        $candidates = mb_strlen($term) >= 2
            ? Student::query()->active()->search($term)
                ->whereDoesntHave('groups', fn (Builder $query) => $query->whereKey($this->group->id))
                ->with('level')
                // Les élèves du niveau du groupe d'abord
                ->orderByRaw('case when level_id = ? then 0 else 1 end', [$this->group->level_id])
                ->orderBy('last_name')
                ->limit(8)
                ->get()
            : collect();

        return view('livewire.teacher.groups.show', [
            'students' => $students,
            'candidates' => $candidates,
            'capacity' => $this->group->capacity(),
            'nextLessons' => $this->group->lessons()
                ->with('room')
                ->where('starts_at', '>=', now())
                ->where('status', '!=', LessonStatus::Cancelled)
                ->chronological()
                ->limit(3)
                ->get(),
            'levelOptions' => Catalog::levels()->pluck('name', 'id')->all(),
        ])->title($this->group->name);
    }
}
