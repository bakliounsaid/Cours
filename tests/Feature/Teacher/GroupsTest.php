<?php

use App\Actions\Lessons\GenerateGroupLessons;
use App\Enums\LessonType;
use App\Livewire\Teacher\Groups\Index;
use App\Livewire\Teacher\Groups\Show;
use App\Models\Group;
use App\Models\Level;
use App\Models\Room;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TimeSlot;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    // Dimanche 1er novembre 2026
    $this->travelTo(Carbon::parse('2026-11-01 08:00'));

    $this->maths = Subject::factory()->create();
    $this->teacher = Teacher::factory()->create(['rate_group' => 2000, 'rate_individual' => 3000]);
    $this->teacher->subjects()->attach($this->maths);
    $this->level = Level::factory()->create();
    $this->room = Room::factory()->create(['capacity' => 2]);
    $this->sunday = TimeSlot::factory()->on(0, '17:00', '19:00')->create();
    $this->tuesday = TimeSlot::factory()->on(2, '17:00', '19:00')->create();

    $this->actingAs($this->teacher->user);
});

function teacherGroup(array $attributes = []): Group
{
    return Group::factory()->create([
        'teacher_id' => test()->teacher->id,
        'subject_id' => test()->maths->id,
        'level_id' => test()->level->id,
        'room_id' => test()->room->id,
        ...$attributes,
    ]);
}

it('affiche les pages de l’espace enseignant', function () {
    $group = teacherGroup();

    $this->get(route('teacher.today'))->assertOk()->assertSee(__('teacher.rates.title'));
    $this->get(route('teacher.groups'))->assertOk()->assertSee($group->name);
    $this->get(route('teacher.groups.show', $group))->assertOk();
    $this->get(route('teacher.students'))->assertOk();
});

it('crée un groupe et planifie ses séances', function () {
    Livewire::test(Index::class)
        ->call('create')
        ->assertSet('form.subject_id', (string) $this->maths->id)
        ->set('form.name', 'Maths 4AM')
        ->set('form.level_id', (string) $this->level->id)
        ->set('form.room_id', (string) $this->room->id)
        ->set('form.time_slot_ids', [(string) $this->sunday->id, (string) $this->tuesday->id])
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('showForm', false);

    $group = Group::query()->where('name', 'Maths 4AM')->sole();

    expect($group->teacher_id)->toBe($this->teacher->id)
        ->and($group->timeSlots)->toHaveCount(2)
        ->and($group->lessons()->where('type', LessonType::Group)->count())->toBeGreaterThanOrEqual(4);
});

it('refuse un créneau où la salle est déjà prise par un autre groupe', function () {
    $other = Group::factory()->create(['room_id' => $this->room->id, 'name' => 'Arabe 5AP']);
    $other->timeSlots()->attach($this->sunday);

    Livewire::test(Index::class)
        ->call('create')
        ->set('form.name', 'Maths 4AM')
        ->set('form.level_id', (string) $this->level->id)
        ->set('form.room_id', (string) $this->room->id)
        ->set('form.time_slot_ids', [(string) $this->sunday->id])
        ->call('save')
        ->assertHasErrors('form.time_slot_ids');

    expect(Group::query()->where('name', 'Maths 4AM')->exists())->toBeFalse();
});

it('refuse une matière que l’enseignant n’enseigne pas', function () {
    Livewire::test(Index::class)
        ->call('create')
        ->set('form.subject_id', (string) Subject::factory()->create()->id)
        ->call('save')
        ->assertHasErrors('form.subject_id');
});

it('archive un groupe et retire ses séances à venir', function () {
    $group = teacherGroup();
    $group->timeSlots()->attach($this->sunday);
    app(GenerateGroupLessons::class)->handle(today(), today()->addDays(13), $group);
    expect($group->lessons()->count())->toBe(2);

    Livewire::test(Index::class)->call('archive', $group->id);

    expect($group->fresh()->is_active)->toBeFalse()
        ->and($group->lessons()->count())->toBe(0);
});

it('ne montre pas les groupes d’un autre enseignant', function () {
    $other = Group::factory()->create();

    $this->get(route('teacher.groups.show', $other))->assertNotFound();
    Livewire::test(Index::class)->call('edit', $other->id)->assertNotFound();
});

it('ajoute et retire un élève, dans la limite des places', function () {
    $group = teacherGroup();
    [$a, $b, $c] = Student::factory()->count(3)->create(['level_id' => $this->level->id]);

    $component = Livewire::test(Show::class, ['group' => $group])
        ->set('studentSearch', $a->last_name)
        ->assertSee($a->full_name)
        ->call('addStudent', $a->id)
        ->call('addStudent', $b->id)
        ->call('addStudent', $c->id)
        ->assertDispatched('toast', type: 'error');

    expect($group->students()->pluck('students.id')->all())->toEqualCanonicalizing([$a->id, $b->id])
        ->and($a->subjects()->whereKey($this->maths->id)->exists())->toBeTrue();

    $component->call('removeStudent', $a->id);

    expect($group->students()->pluck('students.id')->all())->toBe([$b->id]);
});

it('crée un nouvel élève directement dans le groupe', function () {
    $group = teacherGroup();

    Livewire::test(Show::class, ['group' => $group])
        ->call('create')
        ->assertSet('form.level_id', (string) $this->level->id)
        ->set('form.first_name', 'Lina')
        ->set('form.last_name', 'Kaci')
        ->set('form.parent_phone', '0661 23 45 67')
        ->call('save')
        ->assertHasNoErrors();

    $student = Student::query()->where('first_name', 'Lina')->sole();

    expect($student->parent_phone)->toBe('0661234567')
        ->and($group->students()->whereKey($student->id)->exists())->toBeTrue();
});

it('attribue le groupe à son créateur par défaut', function () {
    Livewire::test(Index::class)
        ->call('create')
        ->assertSet('form.teacher_id', (string) $this->teacher->id);
});

it('confie le groupe et ses séances à un collègue de la même matière', function () {
    $colleague = Teacher::factory()->create();
    $colleague->subjects()->attach($this->maths);

    Livewire::test(Index::class)
        ->call('create')
        ->set('form.name', 'Maths 3AM')
        ->set('form.level_id', (string) $this->level->id)
        ->set('form.room_id', (string) $this->room->id)
        ->set('form.time_slot_ids', [(string) $this->sunday->id])
        ->set('form.teacher_id', (string) $colleague->id)
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('toast', type: 'success');

    $group = Group::query()->where('name', 'Maths 3AM')->sole();

    expect($group->teacher_id)->toBe($colleague->id)
        ->and($group->lessons()->pluck('teacher_id')->unique()->all())->toBe([$colleague->id]);

    // Le groupe ne fait plus partie de mes groupes
    Livewire::test(Index::class)->assertDontSee('Maths 3AM');
});

it('refuse de confier le groupe à un enseignant d’une autre matière', function () {
    $other = Teacher::factory()->create();

    Livewire::test(Index::class)
        ->call('create')
        ->set('form.teacher_id', (string) $other->id)
        ->call('save')
        ->assertHasErrors('form.teacher_id');
});
