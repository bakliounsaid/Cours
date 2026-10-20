<?php

namespace App\Livewire\Teacher\Students;

use App\Enums\LessonStatus;
use App\Enums\LessonType;
use App\Enums\Locale;
use App\Enums\PaymentMethod;
use App\Livewire\Concerns\WithModalForm;
use App\Livewire\Forms\IndividualLessonForm;
use App\Livewire\Forms\PaymentForm;
use App\Livewire\Forms\StudentForm;
use App\Livewire\Teacher\Concerns\ForCurrentTeacher;
use App\Models\Lesson;
use App\Models\Student;
use App\Services\LessonCounter;
use App\Support\Catalog;
use App\Support\LessonBalance;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Mes élèves : ceux de mes groupes et ceux que je suis en cours individuels.
 */
#[Layout('components.layouts.teacher')]
class Index extends Component
{
    use ForCurrentTeacher, WithModalForm, WithPagination;

    public StudentForm $form;

    public IndividualLessonForm $lesson;

    public bool $showLessonForm = false;

    public PaymentForm $payment;

    public bool $showPaymentForm = false;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    /** all | group | individual | payment (paiement à régler) */
    #[Url(as: 'suivi', except: 'all')]
    public string $mode = 'all';

    public function mount(): void
    {
        if (request()->boolean('nouveau')) {
            $this->create();
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedMode(): void
    {
        $this->resetPage();
    }

    public function startIndividual(int $id): void
    {
        $student = $this->findModel($id);
        $this->teacher->individualStudents()->syncWithoutDetaching([$student->id]);

        $this->dispatch('toast', type: 'success', message: __('teacher.students.individual_started', ['name' => $student->full_name]));
    }

    public function stopIndividual(int $id): void
    {
        $student = $this->findModel($id);
        $this->teacher->individualStudents()->detach($student->id);

        $this->dispatch('toast', type: 'success', message: __('teacher.students.individual_stopped', ['name' => $student->full_name]));
    }

    public function planLesson(int $id): void
    {
        $this->lesson->start($this->findModel($id)->id);
        $this->showLessonForm = true;
    }

    public function saveLesson(): void
    {
        $this->lesson->save();

        $this->showLessonForm = false;
        $this->dispatch('toast', type: 'success', message: __('teacher.students.lesson_planned'));
    }

    public function recordPayment(int $id): void
    {
        $this->payment->start($this->findModel($id)->id);
        $this->showPaymentForm = true;
    }

    public function savePayment(): void
    {
        $payment = $this->payment->save()->load('student');

        $this->showPaymentForm = false;
        $this->dispatch('toast', type: 'success', message: __('teacher.payments.recorded', [
            'name' => $payment->student->full_name,
            'count' => $payment->lessons_covered,
        ]));
    }

    protected function findModel(int $id): Student
    {
        return $this->teacher->students()->findOrFail($id);
    }

    /** La suppression d'un élève reste réservée à l'administration. */
    protected function performDelete(Model $model): void
    {
        abort(403);
    }

    public function updated(string $property): void
    {
        if (str_starts_with($property, 'form.') || str_starts_with($property, 'lesson.') || str_starts_with($property, 'payment.')) {
            $this->validateOnly($property);
        }
    }

    public function render(): View
    {
        $teacher = $this->teacher;
        $term = trim($this->search);

        // Compteur de séances de tous mes élèves : sert au filtre « paiement à régler » et aux cartes.
        $balances = app(LessonCounter::class)->forTeacher($teacher, $teacher->students()->active()->pluck('students.id'));
        $toPay = array_keys(array_filter($balances, fn (LessonBalance $balance) => $balance->status()->needsAttention()));

        $students = $teacher->students()
            ->active()
            ->with(['level', 'groups' => fn ($query) => $query->where('teacher_id', $teacher->id)->where('is_active', true)])
            ->withExists(['individualTeachers as is_individual' => fn (Builder $query) => $query->whereKey($teacher->id)])
            ->when($term !== '', fn (Builder $query) => $query->search($term))
            ->when($this->mode === 'group', fn (Builder $query) => $query->whereHas('groups', fn (Builder $groups) => $groups->where('teacher_id', $teacher->id)))
            ->when($this->mode === 'individual', fn (Builder $query) => $query->whereHas('individualTeachers', fn (Builder $teachers) => $teachers->whereKey($teacher->id)))
            ->when($this->mode === 'payment', fn (Builder $query) => $query->whereKey($toPay))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(20);

        // Prochain cours individuel de chaque élève affiché
        $nextLessons = Lesson::query()
            ->where('teacher_id', $teacher->id)
            ->where('type', LessonType::Individual)
            ->where('status', LessonStatus::Scheduled)
            ->where('starts_at', '>=', now())
            ->whereHas('students', fn (Builder $query) => $query->whereIn('students.id', $students->pluck('id')))
            ->with('students:id')
            ->chronological()
            ->get()
            ->flatMap(fn (Lesson $lesson) => $lesson->students->map(fn (Student $student) => [$student->id, $lesson]))
            ->unique(0)
            ->mapWithKeys(fn (array $pair) => [$pair[0] => $pair[1]]);

        $students->getCollection()->each(fn (Student $student) => $student->setRelation('nextLesson', $nextLessons->get($student->id)));

        return view('livewire.teacher.students.index', [
            'students' => $students,
            'balances' => $balances,
            'filters' => ['all', 'group', 'individual', 'payment'],
            'methodOptions' => PaymentMethod::options(),
            'localeOptions' => Locale::options(),
            'today' => today()->toDateString(),
            'durationOptions' => [60 => '1 h', 90 => '1 h 30', 120 => '2 h'],
            'counts' => [
                'all' => $teacher->students()->active()->count(),
                'group' => $teacher->students()->active()->whereHas('groups', fn (Builder $groups) => $groups->where('teacher_id', $teacher->id))->count(),
                'individual' => $teacher->individualStudents()->where('is_active', true)->count(),
                'payment' => count($toPay),
            ],
            'levelOptions' => Catalog::levels()->pluck('name', 'id')->all(),
            'groupOptions' => $teacher->groups()->active()->orderBy('name')->pluck('name', 'id')->all(),
            'subjectOptions' => $teacher->subjects()->ordered()->get()->pluck('name', 'id')->all(),
            'roomOptions' => Catalog::rooms()->pluck('name', 'id')->all(),
        ])->title(__('teacher.nav.students'));
    }
}
