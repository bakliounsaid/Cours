{{-- Interrupteur oui/non : <x-ui.toggle wire:model="form.is_active" :label="__('ui.active')" /> --}}
@props(['label', 'hint' => null])

<label class="flex cursor-pointer items-center justify-between gap-4 rounded-xl px-4 py-3 ring-1 ring-olive-100">
    <span>
        <span class="block text-sm font-semibold text-olive-700">{{ $label }}</span>
        @if ($hint)
            <span class="block text-xs text-subtle">{{ $hint }}</span>
        @endif
    </span>
    <span class="relative inline-flex shrink-0">
        <input type="checkbox" {{ $attributes }} class="peer sr-only">
        <span class="h-6 w-11 rounded-full bg-olive-100 transition peer-checked:bg-terra-600 peer-focus-visible:ring-2 peer-focus-visible:ring-terra-300"></span>
        <span class="absolute start-0.5 top-0.5 size-5 rounded-full bg-white shadow transition peer-checked:translate-x-5 rtl:peer-checked:-translate-x-5"></span>
    </span>
</label>
