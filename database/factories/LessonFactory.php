<?php

namespace Database\Factories;

use App\Enums\LessonStatus;
use App\Enums\LessonType;
use App\Models\Lesson;
use App\Models\Room;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Lesson>
 */
class LessonFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = Carbon::parse(fake()->unique()->dateTimeBetween('+1 day', '+1 year')->format('Y-m-d').' 17:00');

        return [
            'subject_id' => Subject::factory(),
            'room_id' => Room::factory(),
            'teacher_id' => Teacher::factory(),
            'date' => $start->toDateString(),
            'starts_at' => $start,
            'ends_at' => $start->copy()->addHours(2),
            'type' => LessonType::Individual,
            'status' => LessonStatus::Scheduled,
        ];
    }

    /**
     * Séance commençant à un instant donné (durée 2 h).
     */
    public function startingAt(Carbon|string $start): static
    {
        $start = Carbon::parse($start);

        return $this->state(fn () => [
            'date' => $start->toDateString(),
            'starts_at' => $start,
            'ends_at' => $start->copy()->addHours(2),
        ]);
    }

    public function status(LessonStatus $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}
