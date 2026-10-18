{{-- <x-ui.input wire:model.live.blur="form.first_name" :label="__('...')" required /> --}}
@props(['label' => null, 'hint' => null, 'required' => false, 'type' => 'text'])

@php
    $name = $attributes->wire('model')->value() ?: $attributes->get('name');
@endphp

<x-ui.field :label="$label" :for="$name" :hint="$hint" :required="$required">
    <input id="{{ $name }}" type="{{ $type }}"
           {{ $attributes->class(['input', 'input-error' => $errors->has($name)]) }}
           @if ($required) required @endif
           @error($name) aria-invalid="true" @enderror>
</x-ui.field>
