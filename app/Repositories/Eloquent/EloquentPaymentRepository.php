<?php

namespace App\Repositories\Eloquent;

use App\Models\Payment;
use App\Models\Student;
use App\Models\Teacher;
use App\Repositories\Contracts\PaymentRepository;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
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

    public function coveredByStudentAndTeacher(array $studentIds): array
    {
        if ($studentIds === []) {
            return [];
        }

        $result = [];

        $rows = DB::table('payments')
            ->whereIn('student_id', $studentIds)
            ->groupBy('student_id', 'teacher_id')
            ->selectRaw('student_id, teacher_id, sum(lessons_covered) as total')
            ->get();

        foreach ($rows as $row) {
            $result[(int) $row->student_id][(int) $row->teacher_id] = (int) $row->total;
        }

        return $result;
    }

    public function forStudent(Student $student): Collection
    {
        return Payment::query()
            ->where('student_id', $student->id)
            ->with(['teacher.user', 'recorder'])
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->get();
    }

    public function totalPaidByStudent(Student $student): int
    {
        return (int) Payment::query()->where('student_id', $student->id)->sum('amount');
    }

    public function totalBetween(CarbonInterface $from, CarbonInterface $to): int
    {
        return (int) Payment::query()->whereBetween('paid_at', [$from, $to])->sum('amount');
    }
}
