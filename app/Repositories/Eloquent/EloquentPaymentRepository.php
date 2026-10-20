<?php

namespace App\Repositories\Eloquent;

use App\Models\Teacher;
use App\Repositories\Contracts\PaymentRepository;
use Illuminate\Support\Facades\DB;

class EloquentPaymentRepository implements PaymentRepository
{
    public function lessonsCoveredByStudent(Teacher $teacher, array $studentIds): array
    {
        if ($studentIds === []) {
            return [];
        }

        return DB::table('payments')
            ->where('teacher_id', $teacher->id)
            ->whereIn('student_id', $studentIds)
            ->groupBy('student_id')
            ->selectRaw('student_id, sum(lessons_covered) as total')
            ->pluck('total', 'student_id')
            ->map(fn ($total) => (int) $total)
            ->all();
    }
}
