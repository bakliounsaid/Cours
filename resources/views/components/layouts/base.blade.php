@props([
    'title' => null,
    'description' => null,
    'bodyClass' => '',
])

@php
    $locale = \App\Enums\Locale::current();
    $fullTitle = match (true) {
        blank($title) => __('app.name').' · '.__('app.slogan'),
        str_contains($title, __('app.name')) => $title,
        default => $title.' · '.__('app.name'),
    };
@endphp

<!DOCTYPE html>
<html lang="{{ $locale->value }}" dir="{{ $locale->direction() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $fullTitle }}</title>
    <meta name="description" content="{{ $description ?? __('app.meta_description') }}">
    <meta name="theme-color" content="#2F5D50">

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('images/pwa/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="{{ __('app.name') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;700;800&family=Baloo+Bhaijaan+2:wght@600;700;800&family=Cairo:wght@400;600;700&family=Nunito:wght@400;600;700;800&display=swap">

    {{ $head ?? '' }}

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body {{ $attributes->merge(['class' => 'min-h-dvh '.$bodyClass]) }}>
    <x-ui.offline-banner />

    {{ $slot }}

    <x-ui.toasts />
</body>
</html>
