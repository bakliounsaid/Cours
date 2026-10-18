@props(['label' => null, 'hint' => null, 'required' => false, 'rows' => 3])

@php
    $name = $attributes->wire('model')->value() ?: $attributes->get('name');
@endphp

<x-ui.field :label="$label" :for="$name" :hint="$hint" :required="$required">
    <textarea id="{{ $name }}" rows="{{ $rows }}"
              {{ $attributes->class(['input', 'input-error' => $errors->has($name)]) }}
              @if ($required) required @endif></textarea>
</x-ui.field>
