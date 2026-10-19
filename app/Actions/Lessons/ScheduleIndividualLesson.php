<?php

namespace App\Actions\Lessons;

use App\Enums\LessonStatus;
use App\Enums\LessonType;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\ConflictChecker;
use App\Support\Format;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Programme un cours individuel : un enseignant, un élève, une salle, un horaire.
 * Refusé si la salle, l'enseignant ou l'élève est déjà pris sur ce créneau.
 */
class ScheduleIndividualLesson
{
    public function __construct(private readonly ConflictChecker $conflicts) {}

    /**
     * @param  array{subject_id: int|string, room_id: int|string, date: string, starts_time: string, duration: int|string}  $data
     *
     * @throws ValidationException
     */
    public function handle(Teacher $teacher, Student $student, array $data, ?int $createdBy = null): Lesson
    {
        $start = Carbon::parse($data['date'].' '.$data['starts_time']);
        $end = $start->copy()->addMinutes((int) $data['duration']);

        if ($conflict = $this->conflicts->roomConflict((int) $data['room_id'], $start, $end)) {
            $this->fail('room_id', __('teacher.conflicts.room', ['time' => Format::timeRange($conflict->starts_at, $conflict->ends_at)]));
        }

        if ($conflict = $this->conflicts->teacherConflict($teacher->id, $start, $end)) {
            $this->fail('starts_time', __('teacher.conflicts.teacher', ['time' => Format::timeRange($conflict->starts_at, $conflict->ends_at)]));
        }

        if ($this->conflicts->studentConflicts([$student->id], $start, $end)->isNotEmpty()) {
            $this->fail('starts_time', __('teacher.conflicts.student', ['name' => $student->full_name]));
        }

        try {
            return DB::transaction(function () use ($teacher, $student, $data, $start, $end, $createdBy) {
                $lesson = Lesson::query()->create([
                    'subject_id' => (int) $data['subject_id'],
                    'room_id' => (int) $data['room_id'],
                    'teacher_id' => $teacher->id,
                    'date' => $start->toDateString(),
                    'starts_at' => $start,
                    'ends_at' => $end,
                    'type' => LessonType::Individual,
                    'status' => LessonStatus::Scheduled,
                    'created_by' => $createdBy,
                ]);

                $lesson->students()->attach($student->id);
                $teacher->individualStudents()->syncWithoutDetaching([$student->id]);

                return $lesson;
            });
        } catch (QueryException $exception) {
            // Créneau pris au même moment (contrainte d'exclusion de la base)
            if ($exception->getCode() !== '23P01') {
                throw $exception;
            }

            $this->fail('starts_time', __('teacher.conflicts.generic'));
        }
    }

    private function fail(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
