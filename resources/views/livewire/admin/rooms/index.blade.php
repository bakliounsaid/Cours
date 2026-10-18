<div>
    <x-ui.page-header :title="__('admin.nav.rooms')" :description="__('admin.rooms.description')">
        <x-slot:actions>
            <button type="button" wire:click="create" class="btn-primary">
                <x-icon name="plus" class="size-4" /> {{ __('admin.rooms.add') }}
            </button>
        </x-slot:actions>
    </x-ui.page-header>

    @include('tables.data-table')

    <x-ui.modal wire:model="showForm" :title="$form->isEditing() ? __('admin.rooms.edit') : __('admin.rooms.add')">
        <form id="room-form" wire:submit="save" class="space-y-4">
            <x-ui.input wire:model.live.blur="form.name" :label="__('admin.rooms.name')" required />
            <x-ui.input wire:model.live.blur="form.capacity" type="number" min="1" :max="config('educoeur.groups.max_students')" :label="__('admin.rooms.capacity')" :hint="__('admin.rooms.capacity_hint', ['max' => config('educoeur.groups.max_students')])" required />
            <x-ui.input wire:model.live.blur="form.color" type="color" :label="__('admin.rooms.color')" class="h-11 p-1" required />
        </form>

        <x-slot:footer>
            <button type="button" class="btn-ghost" x-on:click="open = false">{{ __('ui.cancel') }}</button>
            <button type="submit" form="room-form" class="btn-primary" wire:loading.attr="disabled" wire:target="save">{{ __('ui.save') }}</button>
        </x-slot:footer>
    </x-ui.modal>
</div>
