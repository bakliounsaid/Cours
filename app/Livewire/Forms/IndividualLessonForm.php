<?php

namespace App\Livewire\Forms;

use App\Actions\Lessons\ScheduleIndividualLesson;
use App\Livewire\Forms\Concerns\PrefixesActionErrors;
use App\Models\Lesson;
use App\Models\Teacher;
use Illuminate\Validation\Rule;
use Livewire\Form;

/**
 * Programmation d'un cours individuel par l'enseignant connecté.
 */
class IndividualLessonForm extends Form
{
    use PrefixesActionErrors;

    public ?int $student_id = null;

    public string $subject_id = '';

    public string $room_id = '';

    public string $date = '';

    public string $starts_time = '17:00';

    public int|string $duration = 120;

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'subject_id' => ['required', Rule::in($this->teacher()->subjects()->pluck('subjects.id')->map(fn ($id) => (string) $id)->all())],
            'room_id' => ['required', Rule::exists('rooms', 'id')],
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'starts_time' => ['required', 'date_format:H:i'],
            'duration' => ['required', 'integer', Rule::in([60, 90, 120])],
        ];
    }

    public function start(int $studentId): void
    {
        $this->reset();
        $this->resetErrorBag();
        $this->student_id = $studentId;
        $this->date = today()->toDateString();

        $subjects = $this->teacher()->subjects()->pluck('subjects.id');
        $this->subject_id = $subjects->count() === 1 ? (string) $subjects->first() : '';
    }

    public function save(): Lesson
    {
        $data = $this->validate();
        $student = $this->teacher()->students()->findOrFail($this->student_id);

        return $this->withFormErrors(fn () => app(ScheduleIndividualLesson::class)->handle($this->teacher(), $student, $data, auth()->id()));
    }

    private function teacher(): Teacher
    {
        return auth()->user()->teacher;
    }
}
