<?php

namespace App\Livewire\Admin\Teachers;

use App\Enums\LessonStatus;
use App\Enums\LessonType;
use App\Livewire\Tables\Column;
use App\Livewire\Tables\DataTable;
use App\Livewire\Tables\Filter;
use App\Models\Lesson;
use App\Models\Teacher;
use App\Support\Format;
use App\Support\Phone;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;

/**
 * Fiche enseignant : chiffres du mois et historique complet de ses séances.
 */
#[Layout('components.layouts.admin')]
class Show extends DataTable
{
    #[Locked]
    public Teacher $teacher;

    #[Url(as: 'du', except: '')]
    public string $from = '';

    #[Url(as: 'au', except: '')]
    public string $to = '';

    public function mount(Teacher $teacher): void
    {
        $this->teacher = $teacher->load('user', 'subjects');
    }

    public function updatedFrom(): void
    {
        $this->resetPage();
    }

    public function updatedTo(): void
    {
        $this->resetPage();
    }

    /**
     * Raccourcis de période : mois en cours, mois précédent, tout.
     */
    public function period(string $period): void
    {
        [$this->from, $this->to] = match ($period) {
            'this_month' => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
            'last_month' => [now()->subMonthNoOverflow()->startOfMonth()->toDateString(), now()->subMonthNoOverflow()->endOfMonth()->toDateString()],
            'upcoming' => [today()->toDateString(), ''],
            default => ['', ''],
        };

        $this->resetPage();
    }

    public function resetFilters(): void
    {
        parent::resetFilters();
        $this->reset('from', 'to');
    }

    protected function query(): Builder
    {
        return $this->teacher->lessonHistory()
            ->with(['group', 'subject', 'room', 'checkin', 'students', 'teacher.user', 'originalTeacher.user'])
            ->when($this->validDate($this->from), fn (Builder $query, string $from) => $query->whereDate('date', '>=', $from))
            ->when($this->validDate($this->to), fn (Builder $query, string $to) => $query->whereDate('date', '<=', $to));
    }

    protected function columns(): array
    {
        return [
            Column::make('starts_at', __('admin.lessons.when'))->sortable()
                ->format(fn (Lesson $lesson) => view('livewire.admin.teachers.lesson-when', ['lesson' => $lesson]))
                ->exportAs(fn (Lesson $lesson) => Format::date($lesson->date).' '.Format::timeRange($lesson->starts_at, $lesson->ends_at)),
            Column::make('name', __('admin.lessons.group'))
                ->format(fn (Lesson $lesson) => $lesson->displayName()),
            Column::make('subject', __('admin.lessons.subject'))->hideOnMobile()
                ->format(fn (Lesson $lesson) => $lesson->subject->name),
            Column::make('room', __('admin.lessons.room'))
                ->format(fn (Lesson $lesson) => $lesson->room->name),
            Column::make('type', __('admin.lessons.type'))
                ->format(fn (Lesson $lesson) => view('tables.cells.badge', ['color' => $lesson->type->badgeClasses(), 'label' => $lesson->type->label()]))
                ->exportAs(fn (Lesson $lesson) => $lesson->type->label()),
            Column::make('status', __('ui.status'))->sortable()
                ->format(fn (Lesson $lesson) => view('tables.cells.badge', ['color' => $lesson->status->badgeClasses(), 'label' => $lesson->status->label()]))
                ->exportAs(fn (Lesson $lesson) => $lesson->status->label()),
            Column::make('checkin', __('admin.lessons.checkin'))
                ->format(fn (Lesson $lesson) => $lesson->checkin ? Format::time($lesson->checkin->checked_at) : '—'),
            Column::make('note', __('admin.lessons.note'))->hideOnMobile()
                ->format(fn (Lesson $lesson) => $this->replacementNote($lesson)),
        ];
    }

    protected function filters(): array
    {
        return [
            Filter::make('status', __('ui.status'), LessonStatus::options(),
                fn (Builder $query, string $value) => $query->where('status', $value)),
            Filter::make('type', __('admin.lessons.type'), LessonType::options(),
                fn (Builder $query, string $value) => $query->where('type', $value)),
        ];
    }

    protected function applySearch(Builder $query, string $term): void
    {
        $query->where(function (Builder $query) use ($term) {
            $query->whereHas('group', fn (Builder $groups) => $groups->whereLike('name', "%{$term}%"))
                ->orWhereLike('title', "%{$term}%")
                ->orWhereHas('students', fn (Builder $students) => $students->search($term));
        });
    }

    protected function applyDefaultSort(Builder $query): void
    {
        $query->orderByDesc('starts_at')->orderByDesc('id');
    }

    protected function exportFileName(): string
    {
        return 'seances-'.Str::slug($this->teacher->user->name);
    }

    protected function viewData(): array
    {
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $thisMonth = $this->teacher->lessonHistory()->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()]);

        return [
            'stats' => [
                'done' => (clone $thisMonth)->where('teacher_id', $this->teacher->id)->where('status', LessonStatus::Done)->count(),
                'upcoming' => $this->teacher->lessons()->where('status', LessonStatus::Scheduled)->whereBetween('starts_at', [now(), now()->addDays(7)])->count(),
                'cancelled' => (clone $thisMonth)->where('status', LessonStatus::Cancelled)->count(),
                'earned' => $this->teacher->checkins()->whereBetween('checked_at', [$monthStart, $monthEnd])->sum('rate_amount'),
            ],
            'phone' => $this->teacher->phone ? Phone::format($this->teacher->phone) : null,
        ];
    }

    protected function view(): string
    {
        return 'livewire.admin.teachers.show';
    }

    public function render(): View
    {
        return parent::render()->title($this->teacher->user->name);
    }

    private function replacementNote(Lesson $lesson): string
    {
        if ($lesson->original_teacher_id === $this->teacher->id && $lesson->teacher_id !== $this->teacher->id) {
            return __('admin.lessons.replaced_by', ['name' => $lesson->teacher->user->name]);
        }

        if ($lesson->original_teacher_id && $lesson->teacher_id === $this->teacher->id) {
            return __('admin.lessons.replacing', ['name' => $lesson->originalTeacher?->user->name]);
        }

        return $lesson->cancel_reason ?? '';
    }

    private function validDate(string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
