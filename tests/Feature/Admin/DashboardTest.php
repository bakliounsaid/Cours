<?php

use App\Enums\LessonStatus;
use App\Livewire\Admin\Dashboard;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    // Mardi 3 novembre 2026 → semaine du dimanche 1er au samedi 7
    $this->travelTo(Carbon::parse('2026-11-03 09:00'));
    $this->actingAs(User::factory()->admin()->create());
});

it('affiche le planning de la semaine avec le nom de l’enseignant de chaque séance', function () {
    $karim = Teacher::factory()->for(User::factory()->teacher()->state(['name' => 'Karim Benali']))->create();
    $group = Group::factory()->for($karim)->create(['name' => 'Maths 4AM']);
    Lesson::factory()->for($karim)->for($group)->for($group->room)->startingAt('2026-11-03 17:00')->create();

    Livewire::test(Dashboard::class)
        ->assertSee('Planning de la semaine')
        ->assertSee('Du 1 novembre 2026 au 7 novembre 2026')
        ->assertSeeInOrder(['dimanche', 'lundi', 'mardi', 'Maths 4AM', 'Karim Benali', 'mercredi'])
        ->assertSee('17:00 – 19:00');
});

it('n’affiche pas les séances des autres semaines et permet de naviguer', function () {
    Lesson::factory()->startingAt('2026-11-10 17:00')->create(['title' => 'Séance de la semaine prochaine']);

    Livewire::test(Dashboard::class)
        ->assertDontSee('Séance de la semaine prochaine')
        ->call('nextWeek')
        ->assertSet('week', '2026-11-08')
        ->assertSee('Séance de la semaine prochaine')
        ->call('currentWeek')
        ->assertDontSee('Séance de la semaine prochaine');
});

it('signale les remplacements et les séances annulées', function () {
    $original = Teacher::factory()->for(User::factory()->teacher()->state(['name' => 'Sarah Messaoudi']))->create();
    Lesson::factory()->startingAt('2026-11-04 17:00')->create(['original_teacher_id' => $original->id, 'title' => 'Français 1AM']);
    Lesson::factory()->startingAt('2026-11-05 17:00')->status(LessonStatus::Cancelled)->create(['title' => 'Séance annulée']);

    Livewire::test(Dashboard::class)
        ->assertSee('Remplace Sarah Messaoudi')
        ->assertSee('Annulée');
});

it('s’affiche en arabe avec les jours en arabe', function () {
    app()->setLocale('ar');
    Lesson::factory()->startingAt('2026-11-03 17:00')->create(['title' => 'حصة']);

    Livewire::test(Dashboard::class)
        ->assertSee('جدول حصص الأسبوع')
        ->assertSee('الثلاثاء');
});
