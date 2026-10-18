<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\Level;
use App\Models\Room;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Group>
 */
class GroupFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Groupe '.fake()->unique()->bothify('??-##'),
            'subject_id' => Subject::factory(),
            'level_id' => Level::factory(),
            'teacher_id' => Teacher::factory(),
            'room_id' => Room::factory(),
            'starts_on' => now()->startOfMonth()->toDateString(),
            'ends_on' => null,
            'is_active' => true,
        ];
    }
}
