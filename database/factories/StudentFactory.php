<?php

namespace Database\Factories;

use App\Enums\Locale;
use App\Models\Level;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'level_id' => Level::factory(),
            'parent_phone' => '0'.fake()->randomElement(['5', '6', '7']).fake()->numerify('########'),
            'parent_locale' => fake()->randomElement(Locale::cases()),
            'enrolled_at' => now()->subMonths(2)->toDateString(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
