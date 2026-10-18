<a href="{{ route('admin.teachers.show', $row) }}" wire:navigate title="{{ __('admin.teachers.history') }}"
   class="inline-flex size-9 items-center justify-center rounded-full text-muted transition hover:bg-sand-200 hover:text-olive-800">
    <x-icon name="eye" class="size-[18px]" /><span class="sr-only">{{ __('admin.teachers.history') }}</span>
</a>
<x-ui.icon-button icon="pencil" :label="__('ui.edit')" wire:click="edit({{ $row->id }})" />
<x-ui.icon-button icon="trash" :label="__('ui.delete')" danger
    x-on:click="$dispatch('confirm', { title: {{ Js::from(__('ui.delete_title')) }}, message: {{ Js::from(__('admin.teachers.delete_message', ['name' => $row->user->name])) }}, onConfirm: () => $wire.delete({{ $row->id }}) })" />
