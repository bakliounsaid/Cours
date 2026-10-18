<?php

namespace Database\Factories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subject>
 */
class SubjectFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'slug' => $name,
            'name' => ['fr' => ucfirst($name), 'ar' => null],
            'color' => fake()->hexColor(),
            'icon' => 'book-open',
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
