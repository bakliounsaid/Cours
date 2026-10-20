<?php

namespace App\Livewire\Teacher\Students;

use App\Actions\Students\FollowIndividually;
use App\Enums\PaymentMethod;
use App\Livewire\Concerns\WithModalForm;
use App\Livewire\Forms\IndividualLessonForm;
use App\Livewire\Forms\PaymentForm;
use App\Livewire\Forms\StudentForm;
use App\Livewire\Teacher\Concerns\ForCurrentTeacher;
use App\Models\Student;
use App\Repositories\Contracts\GroupRepository;
use App\Repositories\Contracts\LessonRepository;
use App\Repositories\Contracts\StudentRepository;
use App\Repositories\Contracts\TeacherRepository;
use App\Services\LessonCounter;
use App\Support\Catalog;
use Illuminate\Contracts\View\View;
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
        app(FollowIndividually::class)->start($this->teacher, $student);

        $this->dispatch('toast', type: 'success', message: __('teacher.students.individual_started', ['name' => $student->full_name]));
    }

    public function stopIndividual(int $id): void
    {
        $student = $this->findModel($id);
        app(FollowIndividually::class)->stop($this->teacher, $student);

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
        return app(StudentRepository::class)->findForTeacher($this->teacher, $id);
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
        $repository = app(StudentRepository::class);

        // Compteur de séances de tous mes élèves : sert au filtre « paiement à régler » et aux cartes.
        $balances = app(LessonCounter::class)->forTeacher($teacher, $repository->activeIdsForTeacher($teacher));
        $toPay = LessonCounter::toPay($balances);

        $students = $this->mode === 'payment'
            ? $repository->paginateForTeacher($teacher, StudentRepository::ALL, $this->search, $toPay)
            : $repository->paginateForTeacher($teacher, $this->mode, $this->search);

        // Prochain cours individuel de chaque élève affiché
        $nextLessons = app(LessonRepository::class)->nextIndividualByStudent($teacher, $students->pluck('id'));

        $students->getCollection()->each(fn (Student $student) => $student->setRelation('nextLesson', $nextLessons->get($student->id)));

        return view('livewire.teacher.students.index', [
            'students' => $students,
            'balances' => $balances,
            'filters' => ['all', 'group', 'individual', 'payment'],
            'methodOptions' => PaymentMethod::options(),
            'today' => today()->toDateString(),
            'durationOptions' => [60 => '1 h', 90 => '1 h 30', 120 => '2 h'],
            'counts' => [...$repository->countsForTeacher($teacher), 'payment' => count($toPay)],
            'levelOptions' => Catalog::levels()->pluck('name', 'id')->all(),
            'groupOptions' => app(GroupRepository::class)->optionsForTeacher($teacher),
            'subjectOptions' => app(TeacherRepository::class)->subjectOptions($teacher),
            'roomOptions' => Catalog::rooms()->pluck('name', 'id')->all(),
        ])->title(__('teacher.nav.students'));
    }
}
