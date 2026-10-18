{{--
    Modale liée à une propriété Livewire :
    <x-ui.modal wire:model="showForm" :title="__('...')"> … <x-slot:footer>…</x-slot:footer> </x-ui.modal>
    En plein écran par le bas sur mobile, centrée sur ordinateur.
--}}
@props([
    'title' => null,
    'description' => null,
    'maxWidth' => 'lg',
])

@php
    $model = $attributes->wire('model')->value();
    $width = [
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
    ][$maxWidth];
@endphp

<div x-data="{ open: $wire.entangle('{{ $model }}') }"
     x-show="open"
     x-on:keydown.escape.window="open = false"
     x-cloak
     class="fixed inset-0 z-50 flex items-end justify-center sm:items-center sm:p-6"
     role="dialog"
     aria-modal="true"
     @if ($title) aria-label="{{ $title }}" @endif>
    <div x-show="open"
         x-transition.opacity.duration.200ms
         x-on:click="open = false"
         class="absolute inset-0 bg-olive-900/40 backdrop-blur-sm"></div>

    <div x-show="open"
         x-trap.inert.noscroll="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-6 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-end="opacity-0 translate-y-6 sm:translate-y-0 sm:scale-95"
         {{ $attributes->whereDoesntStartWith('wire:model')->merge(['class' => "relative flex max-h-[92dvh] w-full flex-col rounded-t-3xl bg-white shadow-lift sm:rounded-3xl {$width}"]) }}>
        <div class="flex items-start justify-between gap-4 border-b border-olive-100/70 px-6 py-4">
            <div>
                @if ($title)
                    <h2 class="text-xl font-bold">{{ $title }}</h2>
                @endif
                @if ($description)
                    <p class="mt-0.5 text-sm text-muted">{{ $description }}</p>
                @endif
            </div>
            <button type="button" x-on:click="open = false" class="-me-2 rounded-full p-2 text-subtle hover:bg-sand-200 hover:text-olive-700">
                <x-icon name="x" />
                <span class="sr-only">{{ __('ui.close') }}</span>
            </button>
        </div>

        <div class="overflow-y-auto px-6 py-5">
            {{ $slot }}
        </div>

        @isset($footer)
            <div class="flex flex-col-reverse gap-2 border-t border-olive-100/70 px-6 py-4 pb-[max(1rem,env(safe-area-inset-bottom))] sm:flex-row sm:justify-end">
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>
