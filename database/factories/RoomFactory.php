<?php

namespace Database\Factories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Salle '.fake()->unique()->word(),
            'capacity' => 15,
            'color' => '#2F5D50',
            'sort_order' => 0,
        ];
    }
}
