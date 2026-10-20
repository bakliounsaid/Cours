<?php

namespace App\Actions\Payments;

use App\Enums\PaymentMethod;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;

/**
 * Paiement reçu par un enseignant pour un cycle de séances d'un élève :
 * remet son compteur de séances à zéro.
 */
class RecordPayment
{
    /**
     * @param  array{amount: int|string, method: string, lessons_covered: int|string, notes?: ?string}  $data
     */
    public function handle(Teacher $teacher, Student $student, array $data, User $by): Payment
    {
        return Payment::query()->create([
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'amount' => (int) $data['amount'],
            'paid_at' => now(),
            'method' => PaymentMethod::from($data['method']),
            'lessons_covered' => (int) $data['lessons_covered'],
            'notes' => filled($data['notes'] ?? null) ? trim($data['notes']) : null,
            'recorded_by' => $by->id,
        ]);
    }
}
