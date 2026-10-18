@props([
    'size' => 'md',
    'mono' => false,
    'tagline' => false,
])

@php
    $sizes = [
        'sm' => ['mark' => 'size-8', 'name' => 'text-xl', 'ar' => 'text-xs'],
        'md' => ['mark' => 'size-11', 'name' => 'text-2xl', 'ar' => 'text-sm'],
        'lg' => ['mark' => 'size-20', 'name' => 'text-5xl', 'ar' => 'text-xl'],
    ][$size];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }} dir="ltr">
    <x-brand.mark :mono="$mono" @class([$sizes['mark']]) />
    <span class="flex flex-col leading-none">
        <span @class([$sizes['name'], 'font-[\'Baloo_2\'] font-extrabold tracking-tight', 'text-olive-700' => ! $mono])>Édu<span @class(['text-terra-600' => ! $mono])>cœur</span></span>
        <span @class([$sizes['ar'], 'font-[\'Baloo_Bhaijaan_2\'] font-bold opacity-75', 'text-olive-700' => ! $mono]) dir="rtl" lang="ar">إديكور@if ($tagline) · نُعلّم بقلب@endif</span>
    </span>
</span>
