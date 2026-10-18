{{-- Bouton icône avec libellé accessible : <x-ui.icon-button icon="pencil" :label="__('ui.edit')" wire:click="edit(1)" /> --}}
@props(['icon', 'label', 'danger' => false])

<button type="button" title="{{ $label }}"
        {{ $attributes->class([
            'inline-flex size-9 items-center justify-center rounded-full transition',
            'text-muted hover:bg-sand-200 hover:text-ink' => ! $danger,
            'text-red-500 hover:bg-red-50 hover:text-red-700' => $danger,
        ]) }}>
    <x-icon :name="$icon" class="size-[18px]" />
    <span class="sr-only">{{ $label }}</span>
</button>
