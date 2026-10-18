<?php

namespace Database\Factories;

use App\Enums\Cycle;
use App\Models\Level;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Level>
 */
class LevelFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = fake()->unique()->numberBetween(1, 200);

        return [
            'cycle' => Cycle::Middle,
            'year' => $year,
            'name' => ['fr' => "{$year}e année moyenne", 'ar' => "السنة {$year} متوسط"],
            'sort_order' => $year,
        ];
    }
}
