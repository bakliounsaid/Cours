@props(['title', 'description' => null])

<div {{ $attributes->merge(['class' => 'mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div>
        <h1 class="text-2xl font-extrabold sm:text-3xl">{{ $title }}</h1>
        @if ($description)
            <p class="mt-1 text-muted">{{ $description }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex flex-wrap gap-2">{{ $actions }}</div>
    @endisset
</div>
