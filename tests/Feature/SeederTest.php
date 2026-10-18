<?php

use App\Enums\UserRole;
use App\Models\Group;
use App\Models\Level;
use App\Models\Room;
use App\Models\Subject;
use App\Models\TimeSlot;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoSeeder;

it('installe les données de référence et les comptes de démo', function () {
    $this->seed(DatabaseSeeder::class);

    expect(Room::query()->pluck('capacity', 'name')->all())->toBe(['Portail' => 15, 'Mur' => 15])
        ->and(Subject::count())->toBe(5)
        ->and(Level::count())->toBe(9)
        ->and(TimeSlot::count())->toBe(14)
        ->and(TimeSlot::query()->where('day_of_week', 6)->pluck('starts_at')->all())
        ->toBe(['08:00:00', '10:00:00', '12:30:00', '14:30:00', '16:30:00'])
        ->and(User::query()->where('email', DemoSeeder::ADMIN_EMAIL)->value('role'))->toBe(UserRole::Admin)
        ->and(User::query()->where('email', DemoSeeder::TEACHER_EMAIL)->first()->teacher)->not->toBeNull();

    Group::with('room', 'students')->get()->each(
        fn (Group $group) => expect($group->students->count())->toBeLessThanOrEqual($group->capacity())
    );
});

it('peut être relancé sans créer de doublons', function () {
    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    expect(Room::count())->toBe(2)->and(TimeSlot::count())->toBe(14)->and(Group::count())->toBe(8);
});
