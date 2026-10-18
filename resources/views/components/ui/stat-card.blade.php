@props(['label', 'value', 'icon' => 'chart', 'tone' => 'navy', 'href' => null, 'hint' => null])

@php
    $tones = [
        'navy' => 'bg-olive-50 text-muted',
        'coral' => 'bg-terra-50 text-terra-700',
        'sun' => 'bg-honey-100 text-honey-700',
        'green' => 'bg-emerald-50 text-emerald-600',
        'red' => 'bg-red-50 text-red-600',
    ];
    $classes = 'card flex items-center gap-4 p-5'.($href ? ' transition hover:-translate-y-0.5 hover:shadow-lift' : '');
@endphp

@if ($href)
    <a href="{{ $href }}" wire:navigate {{ $attributes->merge(['class' => $classes]) }}>
@else
    <div {{ $attributes->merge(['class' => $classes]) }}>
@endif
    <span class="flex size-12 shrink-0 items-center justify-center rounded-2xl {{ $tones[$tone] }}">
        <x-icon :name="$icon" class="size-6" />
    </span>
    <span class="min-w-0">
        <span class="block text-sm font-semibold text-muted">{{ $label }}</span>
        <span class="block font-display text-2xl font-extrabold text-ink">{{ $value }}</span>
        @if ($hint)
            <span class="block truncate text-xs text-subtle">{{ $hint }}</span>
        @endif
    </span>
@if ($href)
    </a>
@else
    </div>
@endif
