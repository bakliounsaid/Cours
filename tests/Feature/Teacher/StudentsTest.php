<?php

use App\Enums\LessonType;
use App\Livewire\Teacher\Students\Index;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Level;
use App\Models\Room;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-11-01 08:00'));

    $this->maths = Subject::factory()->create();
    $this->teacher = Teacher::factory()->create(['rate_group' => 2000, 'rate_individual' => 3000]);
    $this->teacher->subjects()->attach($this->maths);
    $this->level = Level::factory()->create();
    $this->room = Room::factory()->create();

    $this->actingAs($this->teacher->user);
});

it('liste uniquement les élèves de ses groupes et de ses cours individuels', function () {
    $group = Group::factory()->create(['teacher_id' => $this->teacher->id]);
    $inGroup = Student::factory()->create(['first_name' => 'Yanis']);
    $group->students()->attach($inGroup, ['joined_at' => today()]);

    $individual = Student::factory()->create(['first_name' => 'Meriem']);
    $this->teacher->individualStudents()->attach($individual);

    Student::factory()->create(['first_name' => 'Inconnu']);

    Livewire::test(Index::class)
        ->assertSee('Yanis')->assertSee('Meriem')->assertDontSee('Inconnu')
        ->set('mode', 'individual')
        ->assertSee('Meriem')->assertDontSee('Yanis');
});

it('crée un élève suivi en cours individuel', function () {
    Livewire::test(Index::class)
        ->call('create')
        ->set('form.first_name', 'Rayan')
        ->set('form.last_name', 'Saidi')
        ->set('form.level_id', (string) $this->level->id)
        ->set('form.parent_phone', '0555 12 34 56')
        ->set('form.individual', true)
        ->call('save')
        ->assertHasNoErrors();

    $student = Student::query()->where('first_name', 'Rayan')->sole();

    expect($this->teacher->individualStudents()->whereKey($student->id)->exists())->toBeTrue()
        ->and($student->parent_locale->value)->toBe('fr');
});

it('exige un groupe ou les cours individuels à la création', function () {
    Livewire::test(Index::class)
        ->call('create')
        ->set('form.first_name', 'Rayan')
        ->set('form.last_name', 'Saidi')
        ->set('form.level_id', (string) $this->level->id)
        ->set('form.parent_phone', '0555 12 34 56')
        ->call('save')
        ->assertHasErrors('form.group_id');
});

it('ne peut pas modifier un élève qui n’est pas le sien', function () {
    Livewire::test(Index::class)->call('edit', Student::factory()->create()->id)->assertNotFound();
});

it('programme un cours individuel et refuse un créneau déjà pris', function () {
    $student = Student::factory()->create();
    $this->teacher->individualStudents()->attach($student);

    Livewire::test(Index::class)
        ->call('planLesson', $student->id)
        ->assertSet('lesson.subject_id', (string) $this->maths->id)
        ->set('lesson.date', '2026-11-03')
        ->set('lesson.starts_time', '17:00')
        ->set('lesson.duration', 90)
        ->set('lesson.room_id', (string) $this->room->id)
        ->call('saveLesson')
        ->assertHasNoErrors()
        ->assertSet('showLessonForm', false);

    $lesson = Lesson::query()->sole();
    expect($lesson->type)->toBe(LessonType::Individual)
        ->and($lesson->ends_at->toDateTimeString())->toBe('2026-11-03 18:30:00')
        ->and($lesson->students->pluck('id')->all())->toBe([$student->id])
        ->and($this->teacher->rateFor($lesson))->toBe(3000);

    // Même salle, horaire qui chevauche
    Livewire::test(Index::class)
        ->call('planLesson', $student->id)
        ->set('lesson.date', '2026-11-03')
        ->set('lesson.starts_time', '18:00')
        ->set('lesson.room_id', (string) $this->room->id)
        ->call('saveLesson')
        ->assertHasErrors('lesson.room_id');

    expect(Lesson::query()->count())->toBe(1);
});

it('applique le tarif de groupe aux séances de groupe', function () {
    $lesson = Lesson::factory()->create([
        'teacher_id' => $this->teacher->id,
        'group_id' => Group::factory()->create(['teacher_id' => $this->teacher->id])->id,
        'type' => LessonType::Group,
    ]);

    expect($this->teacher->rateFor($lesson))->toBe(2000);
});
