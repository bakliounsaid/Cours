<?php

namespace App\Livewire\Admin\Rooms;

use App\Livewire\Concerns\WithModalForm;
use App\Livewire\Forms\RoomForm;
use App\Livewire\Tables\Column;
use App\Livewire\Tables\DataTable;
use App\Models\Room;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.admin')]
class Index extends DataTable
{
    use WithModalForm;

    public RoomForm $form;

    protected function query(): Builder
    {
        return Room::query()->withCount(['groups' => fn (Builder $query) => $query->where('is_active', true)]);
    }

    protected function columns(): array
    {
        return [
            Column::make('name', __('admin.rooms.name'))->sortable()
                ->format(fn (Room $room) => new HtmlString('<span class="inline-flex items-center gap-2 font-bold"><span class="size-3 rounded-full" style="background:'.e($room->color).'"></span>'.e($room->name).'</span>'))
                ->exportAs(fn (Room $room) => $room->name),
            Column::make('capacity', __('admin.rooms.capacity'))->sortable()
                ->format(fn (Room $room) => trans_choice('admin.rooms.places', $room->capacity, ['count' => $room->capacity])),
            Column::make('groups_count', __('admin.rooms.active_groups'))->sortable(),
        ];
    }

    protected function applySearch(Builder $query, string $term): void
    {
        $query->whereLike('name', "%{$term}%");
    }

    protected function applyDefaultSort(Builder $query): void
    {
        $query->ordered();
    }

    protected function rowActionsView(): string
    {
        return 'livewire.admin.rooms.row-actions';
    }

    protected function exportFileName(): string
    {
        return 'salles';
    }

    protected function findModel(int $id): Room
    {
        return Room::query()->findOrFail($id);
    }

    protected function view(): string
    {
        return 'livewire.admin.rooms.index';
    }

    public function render(): View
    {
        return parent::render()->title(__('admin.nav.rooms'));
    }
}
