<?php

namespace App\Livewire\Teacher\Groups;

use App\Actions\Groups\SaveGroup;
use App\Livewire\Concerns\WithModalForm;
use App\Livewire\Forms\GroupForm;
use App\Livewire\Teacher\Concerns\ForCurrentTeacher;
use App\Models\Group;
use App\Models\TimeSlot;
use App\Support\Catalog;
use App\Support\Format;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Mes groupes : création, modification, archivage.
 */
#[Layout('components.layouts.teacher')]
class Index extends Component
{
    use ForCurrentTeacher, WithModalForm;

    public GroupForm $form;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    /** active | archived */
    #[Url(as: 'vue', except: 'active')]
    public string $status = 'active';

    public function mount(): void
    {
        if (request()->boolean('nouveau')) {
            $this->create();
        }
    }

    public function archive(int $id): void
    {
        app(SaveGroup::class)->setActive($this->findModel($id), false);
        $this->dispatch('toast', type: 'success', message: __('teacher.groups.archived'));
    }

    public function restore(int $id): void
    {
        app(SaveGroup::class)->setActive($this->findModel($id), true);
        $this->dispatch('toast', type: 'success', message: __('teacher.groups.restored'));
    }

    /**
     * Un groupe n'est jamais supprimé (historique des séances) : on l'archive.
     */
    public function delete(int $id): void
    {
        $this->archive($id);
    }

    protected function findModel(int $id): Group
    {
        return $this->teacher->groups()->with('timeSlots')->findOrFail($id);
    }

    public function render(): View
    {
        $groups = $this->teacher->groups()
            ->with(['subject', 'level', 'room', 'timeSlots' => fn ($query) => $query->ordered()])
            ->withCount(['students' => fn (Builder $query) => $query->where('is_active', true)])
            ->where('is_active', $this->status !== 'archived')
            ->when(trim($this->search) !== '', fn (Builder $query) => $query->whereLike('name', '%'.trim($this->search).'%'))
            ->orderBy('name')
            ->get();

        return view('livewire.teacher.groups.index', [
            'groups' => $groups,
            'archivedCount' => $this->teacher->groups()->where('is_active', false)->count(),
            'subjectOptions' => $this->teacher->subjects()->ordered()->get()->pluck('name', 'id')->all(),
            'levelOptions' => Catalog::levels()->pluck('name', 'id')->all(),
            'roomOptions' => Catalog::rooms()->mapWithKeys(fn ($room) => [$room->id => $room->name.' · '.trans_choice('admin.rooms.places', $room->capacity, ['count' => $room->capacity])])->all(),
            'slotsByDay' => Catalog::activeTimeSlots()->groupBy('day_of_week')->map(fn ($slots, $day) => [
                'day' => Format::weekday((int) $day),
                'slots' => $slots->mapWithKeys(fn (TimeSlot $slot) => [$slot->id => $slot->startTime().' – '.$slot->endTime()])->all(),
            ]),
        ])->title(__('teacher.nav.groups'));
    }
}
