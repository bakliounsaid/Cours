<?php

use App\Enums\LessonStatus;
use App\Models\Lesson;
use App\Models\Room;
use App\Models\Teacher;
use Illuminate\Database\QueryException;

/*
 * Dernier filet de sécurité : même si un service oubliait une vérification,
 * PostgreSQL refuse deux séances qui se chevauchent dans la même salle ou pour le même enseignant.
 */

it('refuse deux séances qui se chevauchent dans la même salle', function () {
    $room = Room::factory()->create();
    Lesson::factory()->for($room)->startingAt('2026-11-01 17:00')->create();

    expect(fn () => Lesson::factory()->for($room)->startingAt('2026-11-01 18:00')->create())
        ->toThrow(QueryException::class);
});

it('refuse un enseignant sur deux séances simultanées', function () {
    $teacher = Teacher::factory()->create();
    Lesson::factory()->for($teacher)->startingAt('2026-11-01 17:00')->create();

    expect(fn () => Lesson::factory()->for($teacher)->startingAt('2026-11-01 17:00')->create())
        ->toThrow(QueryException::class);
});

it('accepte des séances qui se suivent sans se chevaucher', function () {
    $room = Room::factory()->create();
    Lesson::factory()->for($room)->startingAt('2026-11-07 08:00')->create();
    Lesson::factory()->for($room)->startingAt('2026-11-07 10:00')->create();

    expect($room->lessons()->count())->toBe(2);
});

it('libère le créneau quand une séance est annulée', function () {
    $room = Room::factory()->create();
    Lesson::factory()->for($room)->startingAt('2026-11-01 17:00')->status(LessonStatus::Cancelled)->create();
    Lesson::factory()->for($room)->startingAt('2026-11-01 17:00')->create();

    expect($room->lessons()->count())->toBe(2);
});
