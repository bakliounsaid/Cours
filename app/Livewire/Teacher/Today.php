<?php

namespace App\Livewire\Teacher;

use App\Enums\LessonStatus;
use App\Livewire\Teacher\Concerns\ForCurrentTeacher;
use App\Models\Lesson;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Accueil de l'espace enseignant : séances du jour, chiffres clés et tarifs.
 */
#[Layout('components.layouts.teacher')]
class Today extends Component
{
    use ForCurrentTeacher;

    public function render(): View
    {
        $teacher = $this->teacher;

        $lessons = $teacher->lessons()
            ->with(['group.students' => fn ($query) => $query->active(), 'subject', 'room', 'students'])
            ->whereBetween('starts_at', [today(), today()->addDays(7)->endOfDay()])
            ->notCancelled()
            ->chronological()
            ->get();

        [$today, $upcoming] = $lessons->partition(fn (Lesson $lesson) => $lesson->date->isToday());

        return view('livewire.teacher.today', [
            'today' => $today->values(),
            'upcoming' => $upcoming->take(5)->values(),
            'greeting' => match (true) {
                now()->hour < 12 => __('teacher.today.morning'),
                now()->hour < 18 => __('teacher.today.afternoon'),
                default => __('teacher.today.evening'),
            },
            'stats' => [
                'groups' => $teacher->groups()->active()->count(),
                'students' => $teacher->students()->active()->count(),
                'month_lessons' => $teacher->lessons()
                    ->where('status', LessonStatus::Done)
                    ->whereBetween('date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
                    ->count(),
                'earned' => (int) $teacher->checkins()->whereBetween('checked_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('rate_amount'),
            ],
        ])->title(__('teacher.nav.today'));
    }
}
