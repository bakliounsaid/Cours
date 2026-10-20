<?php

use App\Enums\AttendanceStatus;
use App\Enums\LessonEventType;
use App\Enums\LessonStatus;
use App\Enums\LessonType;
use App\Livewire\Teacher\Lessons\Show;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    $this->teacher = Teacher::factory()->create(['rate_group' => 2000, 'rate_individual' => 3000]);
    $this->group = Group::factory()->create(['teacher_id' => $this->teacher->id]);
    [$this->yanis, $this->lina] = Student::factory()->count(2)->sequence(['first_name' => 'Yanis'], ['first_name' => 'Lina'])->create();
    $this->group->students()->attach([$this->yanis->id, $this->lina->id], ['joined_at' => '2026-10-01']);

    $this->lesson = Lesson::factory()->create([
        'group_id' => $this->group->id,
        'subject_id' => $this->group->subject_id,
        'room_id' => $this->group->room_id,
        'teacher_id' => $this->teacher->id,
        'type' => LessonType::Group,
        'date' => '2026-11-03',
        'starts_at' => '2026-11-03 17:00',
        'ends_at' => '2026-11-03 19:00',
    ]);

    $this->actingAs($this->teacher->user);
});

it('n’ouvre l’appel qu’au début de la séance', function () {
    $this->travelTo(Carbon::parse('2026-11-03 16:30'));

    Livewire::test(Show::class, ['lesson' => $this->lesson])
        ->assertSee(__('teacher.lessons.opens_at', ['time' => '16:50']))
        ->call('saveAttendance')
        ->assertDispatched('toast', type: 'error');

    expect($this->lesson->checkin()->exists())->toBeFalse();
});

it('pointe l’enseignant et enregistre les présences au début de la séance', function () {
    $this->travelTo(Carbon::parse('2026-11-03 17:02'));

    Livewire::test(Show::class, ['lesson' => $this->lesson])
        ->assertSet("marks.{$this->yanis->id}", 'present')
        ->set("marks.{$this->lina->id}", 'absent')
        ->call('saveAttendance')
        ->assertDispatched('toast', type: 'success');

    $lesson = $this->lesson->fresh();
    expect($lesson->status)->toBe(LessonStatus::Done)
        ->and($lesson->checkin->checked_at->format('H:i'))->toBe('17:02')
        ->and($lesson->checkin->rate_amount)->toBe(2000)
        ->and($lesson->attendances()->where('student_id', $this->lina->id)->value('status'))->toBe(AttendanceStatus::Absent)
        ->and($lesson->events()->sole()->type)->toBe(LessonEventType::CheckedIn)
        ->and($lesson->events()->sole()->data)->toEqual(['present' => 1, 'late' => 0, 'absent' => 1]);
});

it('trace chaque correction des présences avec l’avant / après', function () {
    $this->travelTo(Carbon::parse('2026-11-03 17:02'));
    $component = Livewire::test(Show::class, ['lesson' => $this->lesson])
        ->set("marks.{$this->lina->id}", 'absent')
        ->call('saveAttendance');

    $this->travelTo(Carbon::parse('2026-11-03 17:20'));
    $component->set("marks.{$this->lina->id}", 'late')->call('saveAttendance');

    $event = $this->lesson->events()->first();
    expect($this->lesson->events()->count())->toBe(2)
        ->and($event->type)->toBe(LessonEventType::AttendanceUpdated)
        ->and($event->data['changes'])->toEqual([['student' => $this->lina->full_name, 'from' => 'absent', 'to' => 'late']])
        ->and($event->user_id)->toBe($this->teacher->user_id)
        // Le pointage initial n'est pas modifié par la correction
        ->and($this->lesson->checkin()->count())->toBe(1);

    $component->assertSee(__('enums.lesson_event_type.attendance_updated'));
});

it('n’accepte plus l’appel le lendemain', function () {
    $this->travelTo(Carbon::parse('2026-11-04 09:00'));

    Livewire::test(Show::class, ['lesson' => $this->lesson])
        ->assertSee(__('teacher.lessons.missed'))
        ->call('saveAttendance')
        ->assertDispatched('toast', type: 'error');
});

it('enregistre le travail réalisé et garde l’ancienne version', function () {
    $this->travelTo(Carbon::parse('2026-11-03 18:55'));

    $component = Livewire::test(Show::class, ['lesson' => $this->lesson])
        ->set('summary', 'Chapitre 3 : les fractions')
        ->set('homework', 'Exercices 6 et 7')
        ->call('saveWork');

    $component->set('summary', 'Chapitre 3 : fractions et décimaux')->call('saveWork');

    $last = $this->lesson->events()->first();
    expect($this->lesson->fresh()->summary)->toBe('Chapitre 3 : fractions et décimaux')
        ->and($this->lesson->events()->where('type', LessonEventType::WorkRecorded)->count())->toBe(2)
        ->and($last->data['previous_summary'])->toBe('Chapitre 3 : les fractions');
});

it('réserve la fiche de la séance à l’enseignant qui l’assure', function () {
    $this->actingAs(Teacher::factory()->create()->user)
        ->get(route('teacher.lessons.show', $this->lesson))
        ->assertNotFound();
});

it('affiche l’historique des séances passées dans le groupe', function () {
    $this->travelTo(Carbon::parse('2026-11-03 17:02'));
    Livewire::test(Show::class, ['lesson' => $this->lesson])->call('saveAttendance');
    $this->lesson->update(['summary' => 'Chapitre 3 : les fractions']);

    $this->travelTo(Carbon::parse('2026-11-05 10:00'));

    $this->get(route('teacher.groups.show', $this->group))
        ->assertOk()
        ->assertSee('Chapitre 3 : les fractions')
        ->assertSee(__('teacher.lessons.presence', ['present' => 2, 'total' => 2]));
});
