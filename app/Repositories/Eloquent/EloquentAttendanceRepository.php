<?php

namespace App\Repositories\Eloquent;

use App\Enums\AttendanceStatus;
use App\Enums\LessonStatus;
use App\Models\Lesson;
use App\Models\Teacher;
use App\Repositories\Contracts\AttendanceRepository;
use Illuminate\Support\Facades\DB;

class EloquentAttendanceRepository implements AttendanceRepository
{
    public function countByStudentForTeacher(Teacher $teacher, array $studentIds, array $statuses): array
    {
        if ($studentIds === [] || $statuses === []) {
            return [];
        }

        return DB::table('attendances')
            ->join('lessons', 'lessons.id', '=', 'attendances.lesson_id')
            ->where('lessons.teacher_id', $teacher->id)
            ->where('lessons.status', '!=', LessonStatus::Cancelled->value)
            ->whereIn('attendances.student_id', $studentIds)
            ->whereIn('attendances.status', array_map(fn (AttendanceStatus $status) => $status->value, $statuses))
            ->groupBy('attendances.student_id')
            ->selectRaw('attendances.student_id as student_id, count(*) as total')
            ->pluck('total', 'student_id')
            ->map(fn ($total) => (int) $total)
            ->all();
    }

    public function statusesForLesson(Lesson $lesson): array
    {
        return $lesson->attendances()->pluck('status', 'student_id')->all();
    }
}
