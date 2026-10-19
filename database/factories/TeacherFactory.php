<?php

namespace Database\Factories;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Teacher>
 */
class TeacherFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->teacher(),
            'phone' => '05'.fake()->numerify('########'),
            'rate_group' => fake()->randomElement([1500, 2000, 2500]),
            'rate_individual' => fake()->randomElement([2500, 3000]),
            'is_active' => true,
        ];
    }
}
