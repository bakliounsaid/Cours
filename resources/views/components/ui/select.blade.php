{{-- <x-ui.select wire:model.live="form.level_id" :label="__('...')" :options="$levels" placeholder="…" /> --}}
@props(['label' => null, 'hint' => null, 'required' => false, 'options' => [], 'placeholder' => null])

@php
    $name = $attributes->wire('model')->value() ?: $attributes->get('name');
@endphp

<x-ui.field :label="$label" :for="$name" :hint="$hint" :required="$required">
    <select id="{{ $name }}"
            {{ $attributes->class(['input pe-9', 'input-error' => $errors->has($name)]) }}
            @if ($required) required @endif>
        @if ($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $value => $optionLabel)
            <option value="{{ $value }}">{{ $optionLabel }}</option>
        @endforeach
        {{ $slot }}
    </select>
</x-ui.field>
