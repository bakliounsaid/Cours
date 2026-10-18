@props(['color' => 'bg-olive-50 text-olive-700'])

<span {{ $attributes->merge(['class' => "badge {$color}"]) }}>{{ $slot }}</span>
