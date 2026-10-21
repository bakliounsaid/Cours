<?php

namespace App\Repositories\Eloquent;

use App\Enums\AttendanceStatus;
use App\Enums\LessonStatus;
use App\Models\Attendance;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\Teacher;
use App\Repositories\Contracts\AttendanceRepository;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EloquentAttendanceRepository implements AttendanceRepository
{
    public function countByStudentForTeacher(Teacher $teacher, array $studentIds, array $statuses): array
    {
        if ($studentIds === [] || $statuses === []) {
            return [];
        }

        return $this->counted($studentIds, $statuses)
            ->where('lessons.teacher_id', $teacher->id)
            ->groupBy('attendances.student_id')
            ->selectRaw('attendances.student_id as student_id, count(*) as total')
            ->pluck('total', 'student_id')
            ->map(fn ($total) => (int) $total)
            ->all();
    }

    public function countByStudentAndTeacher(array $studentIds, array $statuses): array
    {
        if ($studentIds === [] || $statuses === []) {
            return [];
        }

        $result = [];

        $rows = $this->counted($studentIds, $statuses)
            ->groupBy('attendances.student_id', 'lessons.teacher_id')
            ->selectRaw('attendances.student_id as student_id, lessons.teacher_id as teacher_id, count(*) as total')
            ->get();

        foreach ($rows as $row) {
            $result[(int) $row->student_id][(int) $row->teacher_id] = (int) $row->total;
        }

        return $result;
    }

    public function statsForStudent(Student $student): array
    {
        $counts = $this->counted([$student->id], AttendanceStatus::cases())
            ->groupBy('attendances.status')
            ->selectRaw('attendances.status as status, count(*) as total')
            ->pluck('total', 'status');

        return [
            'present' => (int) ($counts[AttendanceStatus::Present->value] ?? 0),
            'late' => (int) ($counts[AttendanceStatus::Late->value] ?? 0),
            'absent' => (int) ($counts[AttendanceStatus::Absent->value] ?? 0),
        ];
    }

    public function recentForStudent(Student $student, int $limit = 15): Collection
    {
        return Attendance::query()
            ->where('student_id', $student->id)
            ->whereHas('lesson', fn ($query) => $query->notCancelled())
            ->with(['lesson.subject', 'lesson.group', 'lesson.teacher.user'])
            ->join('lessons', 'lessons.id', '=', 'attendances.lesson_id')
            ->select('attendances.*')
            ->orderByDesc('lessons.starts_at')
            ->limit($limit)
            ->get();
    }

    public function statusesForLesson(Lesson $lesson): array
    {
        return $lesson->attendances()->pluck('status', 'student_id')->all();
    }

    /**
     * Présences des élèves donnés, pour les statuts donnés, hors séances annulées.
     *
     * @param  list<int>  $studentIds
     * @param  list<AttendanceStatus>  $statuses
     */
    private function counted(array $studentIds, array $statuses): Builder
    {
        return DB::table('attendances')
            ->join('lessons', 'lessons.id', '=', 'attendances.lesson_id')
            ->where('lessons.status', '!=', LessonStatus::Cancelled->value)
            ->whereIn('attendances.student_id', $studentIds)
            ->whereIn('attendances.status', array_map(fn (AttendanceStatus $status) => $status->value, $statuses));
    }
}
