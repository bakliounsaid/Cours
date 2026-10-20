<?php

namespace App\Livewire\Teacher\Groups;

use App\Actions\Groups\AddStudentToGroup;
use App\Enums\AttendanceStatus;
use App\Enums\LessonStatus;
use App\Enums\Locale;
use App\Livewire\Concerns\WithModalForm;
use App\Livewire\Forms\StudentForm;
use App\Livewire\Teacher\Concerns\ForCurrentTeacher;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Student;
use App\Services\LessonCounter;
use App\Support\Catalog;
use App\Support\Format;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
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

    /**
     * Historique des séances passées du groupe : présences et travail réalisé.
     *
     * @return Collection<int, array{date: string, time: string, status: string, statusClasses: string, presence: ?string, summary: ?string, url: ?string}>
     */
    private function history(): Collection
    {
        return $this->group->lessons()
            ->with('checkin')
            ->withCount([
                'attendances',
                'attendances as present_count' => fn (Builder $query) => $query->whereIn('status', [AttendanceStatus::Present, AttendanceStatus::Late]),
            ])
            ->where('starts_at', '<', now())
            ->orderByDesc('starts_at')
            ->limit(10)
            ->get()
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
            'nextLessons' => $this->group->lessons()
                ->with('room')
                ->where('starts_at', '>=', now())
                ->where('status', '!=', LessonStatus::Cancelled)
                ->chronological()
                ->limit(3)
                ->get(),
            'history' => $this->history(),
            'levelOptions' => Catalog::levels()->pluck('name', 'id')->all(),
            'localeOptions' => Locale::options(),
        ])->title($this->group->name);
    }
}
