<?php

namespace App\Livewire\Teacher\Lessons;

use App\Actions\Lessons\RecordLessonWork;
use App\Actions\Lessons\TakeAttendance;
use App\Enums\AttendanceStatus;
use App\Livewire\Teacher\Concerns\ForCurrentTeacher;
use App\Models\Lesson;
use App\Models\LessonEvent;
use App\Models\Student;
use App\Services\LessonCounter;
use App\Support\Format;
use App\Support\LessonBalance;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Fiche d'une séance : appel au début (pointage), travail réalisé, journal.
 */
#[Layout('components.layouts.teacher')]
class Show extends Component
{
    use ForCurrentTeacher;

    #[Locked]
    public Lesson $lesson;

    /** @var array<int, string> [id élève => present|absent|late] */
    public array $marks = [];

    public string $summary = '';

    public string $homework = '';

    public function mount(Lesson $lesson): void
    {
        // Seul l'enseignant qui assure la séance y a accès.
        abort_unless($lesson->teacher_id === $this->teacher->id, 404);

        $this->lesson = $lesson;
        $this->summary = (string) $lesson->summary;
        $this->homework = (string) $lesson->homework;

        // Présences déjà saisies, sinon « présent » par défaut : on ne coche que les absents.
        // La saisie se fait côté navigateur (instantané), l'envoi au clic sur « Valider l'appel ».
        $saved = $lesson->attendances()->pluck('status', 'student_id');
        foreach ($lesson->roster() as $student) {
            $this->marks[$student->id] = ($saved[$student->id] ?? AttendanceStatus::Present)->value;
        }
    }

    public function saveAttendance(): void
    {
        $firstTime = ! $this->lesson->checkin()->exists();

        try {
            app(TakeAttendance::class)->handle($this->lesson, $this->marks, auth()->user());
        } catch (ValidationException $exception) {
            $this->dispatch('toast', type: 'error', message: collect($exception->errors())->flatten()->first());

            return;
        }

        $this->lesson->refresh();
        $this->dispatch('toast', type: 'success', message: $firstTime ? __('teacher.lessons.checked_in') : __('teacher.lessons.attendance_updated'));

        // Cycle de séances atteint : on le signale tout de suite, pendant la séance.
        $toPay = $this->lesson->roster()->filter(fn (Student $student) => $this->balances()[$student->id]->status()->needsAttention());

        if ($toPay->isNotEmpty()) {
            $this->dispatch('toast', type: 'warning', duration: 9000, message: __('teacher.payments.to_ask', [
                'names' => $toPay->pluck('full_name')->implode(', '),
            ]));
        }
    }

    public function saveWork(): void
    {
        $this->validate([
            'summary' => ['nullable', 'string', 'max:5000'],
            'homework' => ['nullable', 'string', 'max:2000'],
        ]);

        app(RecordLessonWork::class)->handle($this->lesson, $this->summary, $this->homework, auth()->user());

        $this->lesson->refresh();
        $this->dispatch('toast', type: 'success', message: __('teacher.lessons.work_saved'));
    }

    public function render(): View
    {
        $lesson = $this->lesson->load(['group', 'subject', 'room', 'students', 'checkin']);
        $checkedIn = $lesson->isCheckedIn();

        return view('livewire.teacher.lessons.show', [
            'title' => $lesson->displayName(),
            'when' => Format::dayDate($lesson->date, withYear: true).' · '.Format::timeRange($lesson->starts_at, $lesson->ends_at),
            'place' => $lesson->subject->name.' · '.__('admin.dashboard.room', ['name' => $lesson->room->name]),
            'students' => $this->students($lesson),
            'statuses' => array_map(fn (AttendanceStatus $status) => [
                'value' => $status->value,
                'label' => $status->label(),
                'selected' => $status->selectedClasses(),
            ], AttendanceStatus::cases()),
            'canTake' => $lesson->canTakeAttendance(),
            'checkedIn' => $checkedIn,
            'banner' => $this->banner($lesson, $checkedIn),
            'events' => $lesson->events()->with('user')->get()->map(fn (LessonEvent $event) => [
                'label' => $event->type->label(),
                'icon' => $event->type->icon(),
                'at' => Format::dateTime($event->created_at),
                'by' => $event->user?->name ?? __('teacher.lessons.events.system'),
                'details' => $event->details(),
            ]),
        ])->title($lesson->displayName());
    }

    /**
     * @return Collection<int, array{id: int, name: string, initials: string, level: string, sessions: string, alert: ?string, alertClasses: string}>
     */
    private function students(Lesson $lesson): Collection
    {
        $balances = $this->balances();

        return $lesson->roster()->load('level')->map(fn (Student $student) => [
            'id' => $student->id,
            'name' => $student->full_name,
            'initials' => $student->initials(),
            'level' => $student->level->name,
            'sessions' => $balances[$student->id]->progressLabel(),
            'alert' => $balances[$student->id]->alert(),
            'alertClasses' => $balances[$student->id]->status()->badgeClasses(),
        ]);
    }

    /**
     * Compteur de séances de chaque élève chez l'enseignant de la séance.
     *
     * @return array<int, LessonBalance>
     */
    private function balances(): array
    {
        return app(LessonCounter::class)->forTeacher($this->lesson->teacher, $this->lesson->roster()->pluck('id'));
    }

    /**
     * Message d'état de l'appel.
     *
     * @return array{classes: string, icon: string, text: string}
     */
    private function banner(Lesson $lesson, bool $checkedIn): array
    {
        $banner = match (true) {
            $lesson->isCancelled() => ['tone' => 'muted', 'icon' => 'x-circle', 'text' => __('teacher.lessons.cancelled')],
            $checkedIn => ['tone' => 'done', 'icon' => 'check-circle', 'text' => __('teacher.lessons.checked_in_at', ['time' => Format::time($lesson->checkin->checked_at)])],
            $lesson->canTakeAttendance() => ['tone' => 'open', 'icon' => 'clipboard-check', 'text' => __('teacher.lessons.open')],
            now()->lessThan($lesson->attendanceOpensAt()) => ['tone' => 'wait', 'icon' => 'clock', 'text' => __('teacher.lessons.opens_at', ['time' => Format::time($lesson->attendanceOpensAt())])],
            default => ['tone' => 'muted', 'icon' => 'warning', 'text' => __('teacher.lessons.missed')],
        };

        $classes = [
            'done' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'open' => 'bg-terra-50 text-terra-700 ring-terra-200',
            'wait' => 'bg-honey-100 text-honey-700 ring-honey-200',
            'muted' => 'bg-sand-200 text-muted ring-sand-300',
        ];

        return ['classes' => $classes[$banner['tone']], 'icon' => $banner['icon'], 'text' => $banner['text']];
    }
}
