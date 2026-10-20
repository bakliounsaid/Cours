<?php

namespace App\Livewire\Forms;

use App\Actions\Groups\SaveGroup;
use App\Models\Group;
use App\Models\Room;
use App\Models\Teacher;
use App\Services\ConflictChecker;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

/**
 * Groupe d'un enseignant (espace enseignant) : l'enseignant est toujours celui connecté.
 */
class GroupForm extends ModelForm
{
    public string $name = '';

    /** Enseignant à qui le groupe est confié : celui qui le crée, sauf choix contraire. */
    public string $teacher_id = '';

    public string $subject_id = '';

    public string $level_id = '';

    public string $room_id = '';

    /** @var list<string> */
    public array $time_slot_ids = [];

    public string $starts_on = '';

    public bool $is_active = true;

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $teacher = $this->teacher();

        return [
            'name' => ['required', 'string', 'min:2', 'max:80'],
            'subject_id' => ['required', Rule::in($teacher->subjects()->pluck('subjects.id')->map(fn ($id) => (string) $id)->all())],
            'teacher_id' => ['required', Rule::in($this->teacherOptions()->keys()->map(fn ($id) => (string) $id)->all())],
            'level_id' => ['required', Rule::exists('levels', 'id')],
            'room_id' => ['required', Rule::exists('rooms', 'id'), $this->roomFitsStudents(...)],
            'time_slot_ids' => ['required', 'array', 'min:1', $this->slotsAreFree(...)],
            'time_slot_ids.*' => [Rule::exists('time_slots', 'id')->where('is_active', true)],
            'starts_on' => ['required', 'date'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @param  Group  $model
     */
    public function load(Model $model): void
    {
        $this->editingId = $model->id;
        $this->name = $model->name;
        $this->teacher_id = (string) $model->teacher_id;
        $this->subject_id = (string) $model->subject_id;
        $this->level_id = (string) $model->level_id;
        $this->room_id = (string) $model->room_id;
        $this->time_slot_ids = $model->timeSlots->pluck('id')->map(fn ($id) => (string) $id)->all();
        $this->starts_on = $model->starts_on->toDateString();
        $this->is_active = $model->is_active;
    }

    public function resetForm(): void
    {
        parent::resetForm();

        $this->starts_on = today()->toDateString();
        $this->teacher_id = (string) $this->teacher()->id;

        // Une seule matière enseignée : pré-sélectionnée
        $subjects = $this->teacher()->subjects()->pluck('subjects.id');
        $this->subject_id = $subjects->count() === 1 ? (string) $subjects->first() : '';
    }

    public function save(): Group
    {
        $data = $this->validate();

        $group = $this->editingId ? $this->teacher()->groups()->findOrFail($this->editingId) : null;

        return app(SaveGroup::class)->handle($data, $group);
    }

    /**
     * Enseignants actifs de la matière choisie (l'enseignant connecté toujours en premier).
     *
     * @return Collection<int, string>
     */
    public function teacherOptions(): Collection
    {
        $me = $this->teacher();

        $colleagues = Teacher::query()
            ->active()
            ->whereKeyNot($me->id)
            ->when($this->subject_id !== '', fn ($query) => $query->whereHas('subjects', fn ($subjects) => $subjects->whereKey($this->subject_id)))
            ->with('user')
            ->get()
            ->sortBy('name')
            ->mapWithKeys(fn (Teacher $teacher) => [$teacher->id => $teacher->name]);

        return collect([$me->id => __('teacher.groups.me', ['name' => $me->name])])->union($colleagues);
    }

    /** Le groupe sera confié à un collègue. */
    public function isForColleague(): bool
    {
        return $this->teacher_id !== '' && (int) $this->teacher_id !== $this->teacher()->id;
    }

    private function teacher(): Teacher
    {
        return auth()->user()->teacher;
    }

    /**
     * La salle doit pouvoir accueillir les élèves déjà inscrits.
     */
    private function roomFitsStudents(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->editingId) {
            return;
        }

        $capacity = min((int) Room::query()->whereKey($value)->value('capacity'), (int) config('educoeur.groups.max_students'));
        $students = Group::query()->findOrFail($this->editingId)->students()->active()->count();

        if ($students > $capacity) {
            $fail(__('teacher.groups.room_too_small', ['count' => $students, 'capacity' => $capacity]));
        }
    }

    /**
     * Pas deux groupes actifs dans la même salle ou avec le même enseignant sur un créneau.
     */
    private function slotsAreFree(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value) || $this->room_id === '' || ! $this->is_active) {
            return;
        }

        $conflict = app(ConflictChecker::class)->groupSlotConflict(
            array_map('intval', $value),
            (int) $this->room_id,
            (int) ($this->teacher_id ?: $this->teacher()->id),
            $this->editingId,
        );

        if ($conflict) {
            $reason = $conflict['reason'] === 'teacher' && $this->isForColleague() ? 'colleague' : $conflict['reason'];

            $fail(__('teacher.groups.slot_taken_'.$reason, [
                'slot' => $conflict['slot']->label(),
                'group' => $conflict['group']->name,
            ]));
        }
    }
}
