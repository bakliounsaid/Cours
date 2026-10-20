<?php

namespace App\Livewire\Teacher;

use App\Livewire\Teacher\Concerns\ForCurrentTeacher;
use App\Models\Lesson;
use App\Repositories\Contracts\LessonRepository;
use App\Support\Format;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Mon programme : mes séances de la semaine (dimanche → samedi).
 */
#[Layout('components.layouts.teacher')]
class Schedule extends Component
{
    use ForCurrentTeacher;

    /** Premier jour (dimanche) de la semaine affichée, au format Y-m-d. */
    #[Url(as: 'semaine', except: '')]
    public string $week = '';

    public function previousWeek(): void
    {
        $this->week = $this->weekStart()->subWeek()->toDateString();
    }

    public function nextWeek(): void
    {
        $this->week = $this->weekStart()->addWeek()->toDateString();
    }

    public function currentWeek(): void
    {
        $this->week = '';
    }

    private function weekStart(): Carbon
    {
        try {
            $date = $this->week !== '' ? Carbon::createFromFormat('Y-m-d', $this->week) : today();
        } catch (\Throwable) {
            $date = today();
        }

        return $date->startOfWeek(CarbonInterface::SUNDAY)->startOfDay();
    }

    public function render(): View
    {
        $start = $this->weekStart();
        $end = $start->copy()->addDays(6);

        $lessons = app(LessonRepository::class)
            ->forTeacherBetween($this->teacher, $start, $end->copy()->endOfDay(), withCancelled: true)
            ->groupBy(fn (Lesson $lesson) => $lesson->date->toDateString());

        $days = collect(CarbonPeriod::create($start, $end))->map(fn (Carbon $day) => [
            'key' => $day->toDateString(),
            'name' => $day->translatedFormat('l'),
            'date' => $day->translatedFormat('j F'),
            'isToday' => $day->isToday(),
            'lessons' => $lessons->get($day->toDateString(), collect())->map(fn (Lesson $lesson) => [
                'id' => $lesson->id,
                'url' => route('teacher.lessons.show', $lesson),
                'time' => Format::timeRange($lesson->starts_at, $lesson->ends_at),
                'name' => $lesson->displayName(),
                'place' => $lesson->subject->name.' · '.$lesson->room->name,
                'color' => $lesson->room->color,
                'cancelled' => $lesson->isCancelled(),
                'badge' => $lesson->isCancelled() ? $lesson->status->label() : null,
                'badgeClasses' => $lesson->status->badgeClasses(),
            ])->values()->all(),
        ]);

        $count = $lessons->flatten()->reject(fn (Lesson $lesson) => $lesson->isCancelled())->count();

        return view('livewire.teacher.schedule', [
            'days' => $days,
            'range' => __('admin.dashboard.week_range', ['from' => Format::longDate($start), 'to' => Format::longDate($end)]),
            'countLabel' => trans_choice('admin.dashboard.lessons_count', $count, ['count' => $count]),
            'isCurrentWeek' => $start->isSameDay(today()->startOfWeek(CarbonInterface::SUNDAY)),
        ])->title(__('teacher.nav.schedule'));
    }
}
