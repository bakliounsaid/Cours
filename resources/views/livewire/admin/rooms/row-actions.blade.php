<x-ui.icon-button icon="pencil" :label="__('ui.edit')" wire:click="edit({{ $row->id }})" />
<x-ui.icon-button icon="trash" :label="__('ui.delete')" danger
    x-on:click="$dispatch('confirm', { title: {{ Js::from(__('ui.delete_title')) }}, message: {{ Js::from(__('admin.rooms.delete_message', ['name' => $row->name])) }}, onConfirm: () => $wire.delete({{ $row->id }}) })" />
