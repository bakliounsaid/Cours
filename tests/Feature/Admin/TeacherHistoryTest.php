<?php

use App\Enums\LessonStatus;
use App\Livewire\Admin\Teachers\Show;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-11-15 10:00'));
    $this->actingAs(User::factory()->admin()->create());
    $this->teacher = Teacher::factory()->create();
});

it('affiche l’historique des séances de l’enseignant, et seulement les siennes', function () {
    $mine = Group::factory()->for($this->teacher)->create(['name' => 'Maths 4AM']);
    Lesson::factory()->for($this->teacher)->for($mine)->startingAt('2026-11-10 17:00')->status(LessonStatus::Done)->create();
    $other = Group::factory()->create(['name' => 'Anglais 3AM']);
    Lesson::factory()->for($other)->for($other->teacher)->startingAt('2026-11-10 17:00')->create();

    $this->get(route('admin.teachers.show', $this->teacher))
        ->assertOk()
        ->assertSee($this->teacher->user->name)
        ->assertSee('Maths 4AM')
        ->assertDontSee('Anglais 3AM');
});

it('montre aussi les séances où il a été remplacé', function () {
    $substitute = Teacher::factory()->create();
    Lesson::factory()->for($substitute)->startingAt('2026-11-12 17:00')->create([
        'original_teacher_id' => $this->teacher->id,
        'title' => 'Séance remplacée',
    ]);

    Livewire::test(Show::class, ['teacher' => $this->teacher])
        ->assertSee('Séance remplacée')
        ->assertSee(__('admin.lessons.replaced_by', ['name' => $substitute->user->name]));
});

it('filtre l’historique par période', function () {
    Lesson::factory()->for($this->teacher)->startingAt('2026-10-05 17:00')->create(['title' => 'Séance d’octobre']);
    Lesson::factory()->for($this->teacher)->startingAt('2026-11-05 17:00')->create(['title' => 'Séance de novembre']);

    Livewire::test(Show::class, ['teacher' => $this->teacher])
        ->assertSee('Séance d’octobre')->assertSee('Séance de novembre')
        ->call('period', 'this_month')
        ->assertSee('Séance de novembre')->assertDontSee('Séance d’octobre')
        ->call('period', 'last_month')
        ->assertSee('Séance d’octobre')->assertDontSee('Séance de novembre');
});

it('calcule les séances faites et le montant pointé du mois', function () {
    $lesson = Lesson::factory()->for($this->teacher)->startingAt('2026-11-10 17:00')->status(LessonStatus::Done)->create();
    $lesson->checkin()->create(['teacher_id' => $this->teacher->id, 'checked_at' => '2026-11-10 17:05', 'rate_amount' => 2000]);

    Livewire::test(Show::class, ['teacher' => $this->teacher])
        ->assertViewHas('stats', fn (array $stats) => $stats['done'] === 1 && (int) $stats['earned'] === 2000);
});

it('exporte l’historique en Excel', function () {
    Lesson::factory()->for($this->teacher)->startingAt('2026-11-10 17:00')->create();

    Livewire::test(Show::class, ['teacher' => $this->teacher])->call('export')->assertFileDownloaded();
});

it('est interdite aux enseignants', function () {
    $this->actingAs($this->teacher->user)->get(route('admin.teachers.show', $this->teacher))->assertForbidden();
});
