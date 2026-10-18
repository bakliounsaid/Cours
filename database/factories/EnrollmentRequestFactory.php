<?php

namespace Database\Factories;

use App\Enums\EnrollmentStatus;
use App\Enums\Locale;
use App\Models\EnrollmentRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EnrollmentRequest>
 */
class EnrollmentRequestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_name' => fake()->firstName().' '.fake()->lastName(),
            'level_id' => null,
            'subject_ids' => [],
            'parent_phone' => '05'.fake()->numerify('########'),
            'parent_locale' => Locale::Fr,
            'message' => fake()->optional()->sentence(),
            'status' => EnrollmentStatus::New,
        ];
    }
}
