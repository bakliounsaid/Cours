{{-- Cases à cocher multiples : <x-ui.checkbox-group wire:model="form.subject_ids" :options="$subjects" /> --}}
@props(['label' => null, 'hint' => null, 'required' => false, 'options' => [], 'columns' => 2])

@php
    $name = $attributes->wire('model')->value();
@endphp

<x-ui.field :label="$label" :for="$name" :hint="$hint" :required="$required">
    <div @class(['grid gap-2', 'sm:grid-cols-2' => $columns === 2, 'sm:grid-cols-3' => $columns === 3])>
        @foreach ($options as $value => $optionLabel)
            <label wire:key="{{ $name }}-{{ $value }}" class="flex cursor-pointer items-center gap-2.5 rounded-xl px-3 py-2 ring-1 ring-olive-100 transition has-checked:bg-terra-50 has-checked:ring-terra-300">
                <input type="checkbox" value="{{ $value }}" {{ $attributes->whereStartsWith('wire:model') }} class="size-4 rounded accent-terra-500">
                <span class="text-sm font-semibold text-olive-700">{{ $optionLabel }}</span>
            </label>
        @endforeach
    </div>
</x-ui.field>
