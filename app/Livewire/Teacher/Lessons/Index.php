<?php

namespace App\Livewire\Teacher\Lessons;

use App\Livewire\Teacher\Concerns\ForCurrentTeacher;
use App\Models\Lesson;
use App\Repositories\Contracts\GroupRepository;
use App\Repositories\Contracts\LessonRepository;
use App\Support\Format;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Mes séances : à venir, passées (avec l'appel) et annulées.
 */
#[Layout('components.layouts.teacher')]
class Index extends Component
{
    use ForCurrentTeacher, WithPagination;

    private const TABS = [LessonRepository::UPCOMING, LessonRepository::PAST, LessonRepository::CANCELLED];

    /** upcoming | past | cancelled */
    #[Url(as: 'vue', except: 'upcoming')]
    public string $tab = 'upcoming';

    #[Url(as: 'groupe', except: '')]
    public string $group = '';

    public function updatedTab(): void
    {
        $this->tab = in_array($this->tab, self::TABS, true) ? $this->tab : 'upcoming';
        $this->resetPage();
    }

    public function updatedGroup(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $repository = app(LessonRepository::class);
        $groupId = $this->group !== '' ? (int) $this->group : null;
        $lessons = $repository->paginateForTeacher($this->teacher, $this->tab, $groupId);

        return view('livewire.teacher.lessons.index', [
            'lessons' => $lessons,
            'days' => $this->groupByDay($lessons->getCollection()),
            'tabs' => collect(self::TABS)->mapWithKeys(fn (string $tab) => [$tab => [
                'label' => __('teacher.lessons_list.tabs.'.$tab),
                'count' => $repository->countForTeacher($this->teacher, $tab, $groupId),
            ]])->all(),
            'groupOptions' => app(GroupRepository::class)->optionsForTeacher($this->teacher, onlyActive: false),
        ])->title(__('teacher.nav.lessons'));
    }

    /**
     * Séances de la page, regroupées par jour, prêtes à afficher.
     *
     * @param  Collection<int, Lesson>  $lessons
     * @return Collection<int, array{date: string, isToday: bool, lessons: list<array<string, mixed>>}>
     */
    private function groupByDay(Collection $lessons): Collection
    {
        return $lessons
            ->groupBy(fn (Lesson $lesson) => $lesson->date->toDateString())
            ->map(fn (Collection $day) => [
                'date' => Format::dayDate($day->first()->date, withYear: true),
                'isToday' => $day->first()->date->isToday(),
                'lessons' => $day->map(fn (Lesson $lesson) => [
                    'id' => $lesson->id,
                    'url' => route('teacher.lessons.show', $lesson),
                    'time' => Format::timeRange($lesson->starts_at, $lesson->ends_at),
                    'name' => $lesson->displayName(),
                    'place' => $lesson->subject->name.' · '.__('admin.dashboard.room', ['name' => $lesson->room->name]),
                    'color' => $lesson->room->color,
                    'type' => $lesson->type->label(),
                    'typeClasses' => $lesson->type->badgeClasses(),
                    'status' => $lesson->status->label(),
                    'statusClasses' => $lesson->status->badgeClasses(),
                    'cancelled' => $lesson->isCancelled(),
                    'note' => $this->note($lesson),
                ])->values()->all(),
            ])
            ->values();
    }

    /** Appel fait / à faire / présences, ou motif d'annulation. */
    private function note(Lesson $lesson): ?string
    {
        return match (true) {
            $lesson->isCancelled() => $lesson->cancel_reason ? __('teacher.cancel.reason').' : '.$lesson->cancel_reason : null,
            $lesson->isCheckedIn() => __('teacher.lessons.checked_in_at', ['time' => Format::time($lesson->checkin->checked_at)])
                .($lesson->attendances_count > 0 ? ' · '.__('teacher.lessons.presence', ['present' => $lesson->present_count, 'total' => $lesson->attendances_count]) : ''),
            $lesson->canTakeAttendance() => __('teacher.lessons.take_attendance'),
            $lesson->hasEnded() => __('teacher.lessons_list.not_taken'),
            default => null,
        };
    }
}
