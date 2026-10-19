{{--
    Drapeau d'une langue (SVG intégré : les emojis drapeaux ne s'affichent pas sous Windows).
    <x-flag :locale="\App\Enums\Locale::Fr" class="h-4 w-6" />
    fr → France, ar → Algérie, en → Royaume-Uni
--}}
@props(['locale'])

@php
    $id = 'flag-'.\Illuminate\Support\Str::random(6);
    $value = $locale instanceof \App\Enums\Locale ? $locale->value : (string) $locale;
    $viewBox = ['ar' => '0 0 30 20', 'en' => '0 0 60 30'][$value] ?? '0 0 3 2';
@endphp

<svg {{ $attributes->merge(['class' => 'h-4 w-6 shrink-0 overflow-hidden rounded-[3px] shadow-sm ring-1 ring-black/10']) }} aria-hidden="true" preserveAspectRatio="none" viewBox="{{ $viewBox }}">
    @switch($value)
        @case('ar')
            <rect width="15" height="20" fill="#006233" />
            <rect x="15" width="15" height="20" fill="#fff" />
            <mask id="{{ $id }}">
                <rect width="30" height="20" fill="#fff" />
                <circle cx="16.4" cy="10" r="4.1" fill="#000" />
            </mask>
            <circle cx="15" cy="10" r="5" fill="#D21034" mask="url(#{{ $id }})" />
            <polygon points="17.60,7.40 18.19,9.19 20.07,9.20 18.55,10.31 19.13,12.10 17.60,11.00 16.07,12.10 16.65,10.31 15.13,9.20 17.01,9.19" fill="#D21034" />
            @break
        @case('en')
            <clipPath id="{{ $id }}"><path d="M30,15 h30 v15 z v15 h-30 z h-30 v-15 z v-15 h30 z" /></clipPath>
            <rect width="60" height="30" fill="#012169" />
            <path d="M0,0 L60,30 M60,0 L0,30" stroke="#fff" stroke-width="6" />
            <path d="M0,0 L60,30 M60,0 L0,30" clip-path="url(#{{ $id }})" stroke="#C8102E" stroke-width="4" />
            <path d="M30,0 v30 M0,15 h60" stroke="#fff" stroke-width="10" />
            <path d="M30,0 v30 M0,15 h60" stroke="#C8102E" stroke-width="6" />
            @break
        @default
            <rect width="1" height="2" fill="#002395" />
            <rect x="1" width="1" height="2" fill="#fff" />
            <rect x="2" width="1" height="2" fill="#ED2939" />
    @endswitch
</svg>
