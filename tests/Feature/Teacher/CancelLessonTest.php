<?php

use App\Actions\Lessons\GenerateGroupLessons;
use App\Enums\LessonEventType;
use App\Enums\LessonStatus;
use App\Livewire\Teacher\Lessons\Show;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Teacher;
use App\Models\TimeSlot;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    // Dimanche 1er novembre 2026, 8 h
    $this->travelTo(Carbon::parse('2026-11-01 08:00'));

    $this->teacher = Teacher::factory()->create();
    $this->sunday = TimeSlot::factory()->on(0, '17:00', '19:00')->create();
    $this->tuesday = TimeSlot::factory()->on(2, '17:00', '19:00')->create();
    $this->group = Group::factory()->create(['teacher_id' => $this->teacher->id, 'starts_on' => '2026-10-01']);
    $this->group->timeSlots()->attach([$this->sunday->id, $this->tuesday->id]);

    // 3 semaines : dimanches 1, 8, 15 et mardis 3, 10, 17
    app(GenerateGroupLessons::class)->handle(today(), today()->addDays(20));

    $this->actingAs($this->teacher->user);
});

function sundayLesson(string $date): Lesson
{
    return test()->group->lessons()->where('starts_at', "{$date} 17:00:00")->sole();
}

it('annule seulement la séance de cette semaine', function () {
    $lesson = sundayLesson('2026-11-08');

    Livewire::test(Show::class, ['lesson' => $lesson])
        ->call('openCancel')
        ->assertSet('cancelScope', 'once')
        ->set('cancelReason', 'Jour férié')
        ->call('cancelLesson')
        ->assertDispatched('toast', type: 'success');

    expect($lesson->fresh()->status)->toBe(LessonStatus::Cancelled)
        ->and($lesson->fresh()->cancel_reason)->toBe('Jour férié')
        ->and(sundayLesson('2026-11-15')->status)->toBe(LessonStatus::Scheduled)
        ->and($this->group->timeSlots()->whereKey($this->sunday->id)->exists())->toBeTrue()
        ->and($lesson->events()->sole()->type)->toBe(LessonEventType::Cancelled);

    // La génération automatique ne recrée pas la séance annulée.
    app(GenerateGroupLessons::class)->handle(today(), today()->addDays(20));
    expect($this->group->lessons()->where('starts_at', '2026-11-08 17:00:00')->count())->toBe(1);
});

it('annule définitivement le créneau : cette séance et les suivantes', function () {
    $lesson = sundayLesson('2026-11-08');

    Livewire::test(Show::class, ['lesson' => $lesson])
        ->call('openCancel')
        ->set('cancelScope', 'permanent')
        ->call('cancelLesson')
        ->assertDispatched('toast', type: 'success');

    expect(sundayLesson('2026-11-01')->status)->toBe(LessonStatus::Scheduled)
        ->and(sundayLesson('2026-11-08')->status)->toBe(LessonStatus::Cancelled)
        ->and(sundayLesson('2026-11-15')->status)->toBe(LessonStatus::Cancelled)
        ->and(sundayLesson('2026-11-15')->events()->sole()->data['scope'])->toBe('permanent')
        // Le créneau du mardi n'est pas touché
        ->and($this->group->lessons()->where('time_slot_id', $this->tuesday->id)->where('status', LessonStatus::Scheduled)->count())->toBe(3)
        ->and($this->group->timeSlots()->pluck('time_slots.id')->all())->toBe([$this->tuesday->id]);

    // Plus aucune séance du dimanche n'est générée ensuite.
    app(GenerateGroupLessons::class)->handle(today(), today()->addDays(40));
    expect($this->group->lessons()->where('time_slot_id', $this->sunday->id)->where('status', LessonStatus::Scheduled)->count())->toBe(1);
});

it('refuse d’annuler une séance dont l’appel est fait', function () {
    $lesson = sundayLesson('2026-11-01');
    $this->travelTo(Carbon::parse('2026-11-01 17:05'));
    Livewire::test(Show::class, ['lesson' => $lesson])->call('saveAttendance');

    Livewire::test(Show::class, ['lesson' => $lesson])
        ->assertDontSeeHtml('wire:click="openCancel"')
        ->call('cancelLesson')
        ->assertDispatched('toast', type: 'error');

    expect($lesson->fresh()->status)->toBe(LessonStatus::Done);
});

it('ne propose l’annulation définitive que pour une séance de groupe régulière', function () {
    $individual = Lesson::factory()->create(['teacher_id' => $this->teacher->id, 'starts_at' => '2026-11-05 10:00', 'ends_at' => '2026-11-05 11:00', 'date' => '2026-11-05']);

    Livewire::test(Show::class, ['lesson' => $individual])
        ->assertDontSee(__('teacher.cancel.scope_permanent'))
        ->set('cancelScope', 'permanent')
        ->call('cancelLesson')
        ->assertDispatched('toast', type: 'error');

    expect($individual->fresh()->status)->toBe(LessonStatus::Scheduled);
});
