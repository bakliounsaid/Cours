<?php

namespace App\Livewire\Teacher\Groups;

use App\Actions\Groups\AddStudentToGroup;
use App\Actions\Groups\RemoveStudentFromGroup;
use App\Livewire\Concerns\WithModalForm;
use App\Livewire\Forms\StudentForm;
use App\Livewire\Teacher\Concerns\ForCurrentTeacher;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Student;
use App\Repositories\Contracts\LessonRepository;
use App\Repositories\Contracts\StudentRepository;
use App\Services\LessonCounter;
use App\Support\Catalog;
use App\Support\Format;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
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
        $student = app(StudentRepository::class)->findActive($studentId);

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
        $student = $this->findModel($studentId);
        app(RemoveStudentFromGroup::class)->handle($this->group, $student);

        $this->dispatch('toast', type: 'success', message: __('teacher.groups.student_removed', ['name' => $student->full_name]));
    }

    /** Un enseignant ne supprime pas un élève : il le retire du groupe. */
    public function delete(int $id): void
    {
        $this->removeStudent($id);
    }

    /** Élève de ce groupe, sinon 404. */
    protected function findModel(int $id): Student
    {
        return app(StudentRepository::class)->findInGroup($this->group, $id);
    }

    protected function performDelete(Model $model): void
    {
        abort(403);
    }

    /**
     * Historique des séances passées du groupe : présences et travail réalisé.
     *
     * @return Collection<int, array{date: string, time: string, status: string, statusClasses: string, presence: ?string, summary: ?string, url: ?string}>
     */
    private function history(): Collection
    {
        return app(LessonRepository::class)->pastForGroup($this->group)
            ->map(fn (Lesson $lesson) => [
                'date' => Format::dayDate($lesson->date),
                'time' => Format::timeRange($lesson->starts_at, $lesson->ends_at),
                'status' => $lesson->status->label(),
                'statusClasses' => $lesson->status->badgeClasses(),
                'presence' => $lesson->attendances_count > 0
                    ? __('teacher.lessons.presence', ['present' => $lesson->present_count, 'total' => $lesson->attendances_count])
                    : null,
                'summary' => $lesson->summary ? Str::limit($lesson->summary, 140) : null,
                // Une séance confiée à un autre enseignant reste visible, sans accès à sa fiche.
                'url' => $lesson->teacher_id === $this->teacher->id ? route('teacher.lessons.show', $lesson) : null,
            ]);
    }

    public function render(): View
    {
        $this->group->load(['subject', 'level', 'room', 'timeSlots' => fn ($query) => $query->ordered()]);

        $students = app(StudentRepository::class)->rosterOfGroup($this->group);
        $term = trim($this->studentSearch);

        $candidates = mb_strlen($term) >= 2
            ? app(StudentRepository::class)->candidatesForGroup($this->group, $term)
            : collect();

        $capacity = $this->group->capacity();

        return view('livewire.teacher.groups.show', [
            'students' => $students,
            'balances' => app(LessonCounter::class)->forTeacher($this->teacher, $students->pluck('id')),
            'candidates' => $candidates,
            'count' => $students->count(),
            'capacity' => $capacity,
            'full' => $students->count() >= $capacity,
            // Jauge circulaire : longueur du trait sur une circonférence de 97,4 (r = 15,5)
            'gauge' => $capacity > 0 ? round(min(1, $students->count() / $capacity) * 97.4, 1) : 0,
            'showSearchResults' => mb_strlen($term) >= 2,
            'nextLessons' => app(LessonRepository::class)->upcomingForGroup($this->group),
            'history' => $this->history(),
            'levelOptions' => Catalog::levels()->pluck('name', 'id')->all(),
        ])->title($this->group->name);
    }
}
