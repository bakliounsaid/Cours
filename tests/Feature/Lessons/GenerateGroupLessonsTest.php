<?php

use App\Actions\Lessons\GenerateGroupLessons;
use App\Enums\LessonStatus;
use App\Enums\LessonType;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\TimeSlot;
use Illuminate\Support\Carbon;

beforeEach(function () {
    // Dimanche 1er novembre 2026
    $this->travelTo(Carbon::parse('2026-11-01 08:00'));
    $this->sunday = TimeSlot::factory()->on(0, '17:00', '19:00')->create();
    $this->tuesday = TimeSlot::factory()->on(2, '17:00', '19:00')->create();
});

function groupOn(array $slots, array $attributes = []): Group
{
    $group = Group::factory()->create(['starts_on' => '2026-10-01', ...$attributes]);
    $group->timeSlots()->attach(collect($slots)->pluck('id'));

    return $group;
}

it('crée les séances de chaque créneau récurrent sur la période', function () {
    $group = groupOn([$this->sunday, $this->tuesday]);

    $report = app(GenerateGroupLessons::class)->handle(today(), today()->addDays(13));

    expect($report['created'])->toBe(4)
        ->and($group->lessons()->orderBy('starts_at')->pluck('starts_at')->map->toDateTimeString()->all())
        ->toBe(['2026-11-01 17:00:00', '2026-11-03 17:00:00', '2026-11-08 17:00:00', '2026-11-10 17:00:00']);

    $lesson = $group->lessons()->first();
    expect($lesson->type)->toBe(LessonType::Group)
        ->and($lesson->status)->toBe(LessonStatus::Scheduled)
        ->and($lesson->teacher_id)->toBe($group->teacher_id)
        ->and($lesson->room_id)->toBe($group->room_id)
        ->and($lesson->ends_at->toDateTimeString())->toBe('2026-11-01 19:00:00');
});

it('ne crée jamais deux fois la même séance', function () {
    groupOn([$this->sunday]);
    $generate = app(GenerateGroupLessons::class);

    $generate->handle(today(), today()->addDays(13));
    $report = $generate->handle(today(), today()->addDays(13));

    expect($report['created'])->toBe(0)->and(Lesson::count())->toBe(2);
});

it('ignore et signale une séance en conflit de salle ou d’enseignant', function () {
    $group = groupOn([$this->sunday]);
    Lesson::factory()->for($group->room)->startingAt('2026-11-01 18:00')->create();

    $report = app(GenerateGroupLessons::class)->handle(today(), today());

    expect($report['created'])->toBe(0)
        ->and($report['skipped'][0]['reason'])->toBe('room');
});

it('respecte les dates de début et de fin du groupe', function () {
    groupOn([$this->sunday], ['starts_on' => '2026-11-05', 'ends_on' => '2026-11-10']);

    app(GenerateGroupLessons::class)->handle(today(), today()->addDays(20));

    expect(Lesson::query()->pluck('date')->map->toDateString()->all())->toBe(['2026-11-08']);
});

it('ne génère rien pour un groupe inactif', function () {
    groupOn([$this->sunday], ['is_active' => false]);

    app(GenerateGroupLessons::class)->handle(today(), today()->addDays(13));

    expect(Lesson::count())->toBe(0);
});

it('est planifiée chaque jour', function () {
    $this->artisan('schedule:list')->expectsOutputToContain('lessons:generate');
});
