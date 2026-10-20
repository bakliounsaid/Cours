<?php

namespace App\Actions\Enrollment;

use App\Enums\EnrollmentStatus;
use App\Enums\Locale;
use App\Models\EnrollmentRequest;
use App\Support\Phone;

/**
 * Enregistre une demande d'inscription envoyée depuis la page d'accueil.
 */
class SubmitEnrollmentRequest
{
    /**
     * @param  array{student_name: string, level_id: int|string|null, subject_ids: list<int|string>, parent_phone: string, message?: ?string}  $data
     */
    public function handle(array $data): EnrollmentRequest
    {
        return EnrollmentRequest::query()->create([
            'student_name' => trim($data['student_name']),
            'level_id' => $data['level_id'] ?: null,
            'subject_ids' => array_values(array_map('intval', $data['subject_ids'])),
            'parent_phone' => Phone::normalize($data['parent_phone']),
            // La langue n'est plus demandée : celle de la page où le parent a rempli le formulaire
            'parent_locale' => Locale::current(),
            'message' => filled($data['message'] ?? null) ? trim($data['message']) : null,
            'status' => EnrollmentStatus::New,
        ]);
    }
}
