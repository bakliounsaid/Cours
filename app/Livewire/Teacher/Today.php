<?php

namespace App\Livewire\Teacher;

use App\Enums\LessonStatus;
use App\Livewire\Teacher\Concerns\ForCurrentTeacher;
use App\Models\Lesson;
use App\Services\LessonCounter;
use App\Support\Format;
use App\Support\LessonBalance;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
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
            ->with(['group.students' => fn ($query) => $query->active(), 'subject', 'room', 'students', 'checkin'])
            ->whereBetween('starts_at', [today(), today()->addDays(7)->endOfDay()])
            ->notCancelled()
            ->chronological()
            ->get();

        [$today, $upcoming] = $lessons->partition(fn (Lesson $lesson) => $lesson->date->isToday());

        $greeting = match (true) {
            now()->hour < 12 => __('teacher.today.morning'),
            now()->hour < 18 => __('teacher.today.afternoon'),
            default => __('teacher.today.evening'),
        };

        return view('livewire.teacher.today', [
            'today' => $today->values(),
            'upcoming' => $upcoming->take(5)->values(),
            'title' => $greeting.', '.Str::before(auth()->user()->name, ' '),
            'date' => Format::dayDate(now(), withYear: true),
            'summary' => trans_choice('teacher.today.summary', $today->count(), ['count' => $today->count()]),
            'tiles' => $this->tiles(),
            'paymentAlert' => $this->paymentAlert(),
            'rates' => [
                'group' => Format::money($teacher->rate_group),
                'individual' => Format::money($teacher->rate_individual),
            ],
        ])->title(__('teacher.nav.today'));
    }

    /** « 3 élèves ont atteint leur cycle de séances » (null si personne). */
    private function paymentAlert(): ?string
    {
        $balances = app(LessonCounter::class)->forTeacher($this->teacher, $this->teacher->students()->active()->pluck('students.id'));
        $count = count(array_filter($balances, fn (LessonBalance $balance) => $balance->status()->needsAttention()));

        return $count > 0 ? trans_choice('teacher.payments.dashboard_alert', $count, ['count' => $count]) : null;
    }

    /**
     * Chiffres clés du bandeau d'accueil.
     *
     * @return list<array{label: string, icon: string, value: int, display: string, unit: ?string, url: ?string}>
     */
    private function tiles(): array
    {
        $teacher = $this->teacher;
        $month = [now()->startOfMonth(), now()->endOfMonth()];

        $tiles = [
            [
                'label' => __('teacher.today.stats.groups'),
                'icon' => 'user-group',
                'value' => $teacher->groups()->active()->count(),
                'unit' => null,
                'url' => route('teacher.groups'),
            ],
            [
                'label' => __('teacher.today.stats.students'),
                'icon' => 'users',
                'value' => $teacher->students()->active()->count(),
                'unit' => null,
                'url' => route('teacher.students'),
            ],
            [
                'label' => __('teacher.today.stats.month_lessons'),
                'icon' => 'check-circle',
                'value' => $teacher->lessons()
                    ->where('status', LessonStatus::Done)
                    ->whereBetween('date', [$month[0]->toDateString(), $month[1]->toDateString()])
                    ->count(),
                'unit' => null,
                'url' => null,
            ],
            [
                'label' => __('teacher.today.stats.earned'),
                'icon' => 'banknotes',
                'value' => (int) $teacher->checkins()->whereBetween('checked_at', $month)->sum('rate_amount'),
                'unit' => __('app.currency'),
                'url' => null,
            ],
        ];

        // Valeur affichée avant l'animation (et sans JavaScript) : « 16 000 »
        return array_map(fn (array $tile) => [...$tile, 'display' => number_format($tile['value'], 0, ',', ' ')], $tiles);
    }
}
