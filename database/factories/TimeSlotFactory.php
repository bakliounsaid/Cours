<?php

namespace Database\Factories;

use App\Models\TimeSlot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TimeSlot>
 */
class TimeSlotFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'day_of_week' => fake()->numberBetween(0, 4),
            'starts_at' => '17:00:00',
            'ends_at' => '19:00:00',
            'is_active' => true,
        ];
    }

    /**
     * Créneau de 2 h un jour donné (0 = dimanche … 6 = samedi).
     */
    public function on(int $dayOfWeek, string $start = '17:00', string $end = '19:00'): static
    {
        return $this->state(fn () => [
            'day_of_week' => $dayOfWeek,
            'starts_at' => $start.':00',
            'ends_at' => $end.':00',
        ]);
    }
}
