<?php

namespace App\Livewire\Teacher;

use App\Livewire\Teacher\Concerns\ForCurrentTeacher;
use App\Models\Lesson;
use App\Repositories\Contracts\GroupRepository;
use App\Repositories\Contracts\LessonRepository;
use App\Repositories\Contracts\StudentRepository;
use App\Services\LessonCounter;
use App\Support\Format;
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

        $lessons = app(LessonRepository::class)->forTeacherBetween($teacher, today(), today()->addDays(7)->endOfDay());

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
        ])->title(__('teacher.nav.dashboard'));
    }

    /** « 3 élèves ont atteint leur cycle de séances » (null si personne). */
    private function paymentAlert(): ?string
    {
        $balances = app(LessonCounter::class)->forTeacher($this->teacher, app(StudentRepository::class)->activeIdsForTeacher($this->teacher));
        $count = count(LessonCounter::toPay($balances));

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
        $lessons = app(LessonRepository::class);
        [$monthStart, $monthEnd] = [now()->startOfMonth(), now()->endOfMonth()];

        $tiles = [
            [
                'label' => __('teacher.today.stats.groups'),
                'icon' => 'user-group',
                'value' => app(GroupRepository::class)->countForTeacher($teacher),
                'unit' => null,
                'url' => route('teacher.groups'),
            ],
            [
                'label' => __('teacher.today.stats.students'),
                'icon' => 'users',
                'value' => app(StudentRepository::class)->countsForTeacher($teacher)['all'],
                'unit' => null,
                'url' => route('teacher.students'),
            ],
            [
                'label' => __('teacher.today.stats.month_lessons'),
                'icon' => 'check-circle',
                'value' => $lessons->doneCountForTeacher($teacher, $monthStart, $monthEnd),
                'unit' => null,
                'url' => null,
            ],
            [
                'label' => __('teacher.today.stats.earned'),
                'icon' => 'banknotes',
                'value' => $lessons->earnedByTeacher($teacher, $monthStart, $monthEnd),
                'unit' => __('app.currency'),
                'url' => null,
            ],
        ];

        // Valeur affichée avant l'animation (et sans JavaScript) : « 16 000 »
        return array_map(fn (array $tile) => [...$tile, 'display' => number_format($tile['value'], 0, ',', ' ')], $tiles);
    }
}
