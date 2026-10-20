<?php

namespace App\Livewire\Teacher\Groups;

use App\Actions\Groups\SaveGroup;
use App\Livewire\Concerns\WithModalForm;
use App\Livewire\Forms\GroupForm;
use App\Livewire\Teacher\Concerns\ForCurrentTeacher;
use App\Models\Group;
use App\Models\TimeSlot;
use App\Repositories\Contracts\GroupRepository;
use App\Repositories\Contracts\TeacherRepository;
use App\Support\Catalog;
use App\Support\Format;
use Illuminate\Contracts\View\View;
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

    /**
     * Un groupe confié à un collègue quitte la liste : on le dit clairement.
     */
    public function save(): void
    {
        $group = $this->form->save()->load('teacher.user');

        $this->showForm = false;
        $this->dispatch('toast', type: 'success', message: $group->teacher_id === $this->teacher->id
            ? __('ui.saved')
            : __('teacher.groups.assigned', ['name' => $group->name, 'teacher' => $group->teacher->name]));
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
        return app(GroupRepository::class)->findForTeacher($this->teacher, $id);
    }

    public function render(): View
    {
        $repository = app(GroupRepository::class);
        $groups = $repository->forTeacher($this->teacher, active: $this->status !== 'archived', search: $this->search);

        return view('livewire.teacher.groups.index', [
            'groups' => $groups,
            'archivedCount' => $repository->countForTeacher($this->teacher, active: false),
            'groupRate' => __('teacher.rates.group_value', ['amount' => Format::money($this->teacher->rate_group)]),
            'subjectOptions' => app(TeacherRepository::class)->subjectOptions($this->teacher),
            'teacherOptions' => $this->showForm ? $this->form->teacherOptions()->all() : [],
            'assignHint' => $this->form->isForColleague() ? __('teacher.groups.assign_colleague_hint') : __('teacher.groups.assign_hint'),
            'levelOptions' => Catalog::levels()->pluck('name', 'id')->all(),
            'roomOptions' => Catalog::rooms()->mapWithKeys(fn ($room) => [$room->id => $room->name.' · '.trans_choice('admin.rooms.places', $room->capacity, ['count' => $room->capacity])])->all(),
            'slotsByDay' => Catalog::activeTimeSlots()->groupBy('day_of_week')->map(fn ($slots, $day) => [
                'day' => Format::weekday((int) $day),
                'slots' => $slots->mapWithKeys(fn (TimeSlot $slot) => [$slot->id => $slot->startTime().' – '.$slot->endTime()])->all(),
            ]),
        ])->title(__('teacher.nav.groups'));
    }
}
