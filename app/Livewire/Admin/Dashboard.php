<?php

namespace App\Livewire\Admin;

use App\Enums\EnrollmentStatus;
use App\Enums\LessonStatus;
use App\Models\EnrollmentRequest;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\Teacher;
use App\Support\Catalog;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Tableau de bord : chiffres clés et planning de la semaine (dimanche → samedi).
 */
#[Layout('components.layouts.admin')]
class Dashboard extends Component
{
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
        $end = $start->copy()->addDays(6)->endOfDay();

        $lessons = Lesson::query()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->with(['group', 'subject', 'room', 'teacher.user', 'originalTeacher.user', 'students'])
            ->chronological()
            ->get()
            ->groupBy(fn (Lesson $lesson) => $lesson->date->toDateString());

        $days = collect(CarbonPeriod::create($start, $start->copy()->addDays(6)))
            ->map(fn (Carbon $day) => ['date' => $day, 'lessons' => $lessons->get($day->toDateString(), collect())]);

        return view('livewire.admin.dashboard', [
            'days' => $days,
            'weekStart' => $start,
            'weekEnd' => $end,
            'isCurrentWeek' => $start->isSameDay(today()->startOfWeek(CarbonInterface::SUNDAY)),
            'rooms' => Catalog::rooms(),
            'weekCount' => $lessons->flatten()->reject(fn (Lesson $lesson) => $lesson->isCancelled())->count(),
            'stats' => [
                'today' => Lesson::query()->onDate(today())->notCancelled()->count(),
                'teachers' => Teacher::query()->active()->count(),
                'students' => Student::query()->active()->count(),
                'enrollments' => EnrollmentRequest::query()->where('status', EnrollmentStatus::New)->count(),
                'needs_action' => Lesson::query()->where('status', LessonStatus::NeedsAction)->count(),
            ],
        ])->title(__('admin.nav.dashboard'));
    }
}
