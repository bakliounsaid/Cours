@php
    $locale = \App\Enums\Locale::current();
    $settings = app(\App\Settings\Settings::class);
    $canonical = route('home.localized', $locale->value);
    $pageTitle = $title ?? __('home.seo_title');
@endphp

<x-layouts.base :title="$pageTitle" class="bg-sand-100">
    <x-slot:head>
        <link rel="canonical" href="{{ $canonical }}">
        @foreach (\App\Enums\Locale::cases() as $alternate)
            <link rel="alternate" hreflang="{{ $alternate->value }}" href="{{ route('home.localized', $alternate->value) }}">
        @endforeach
        <link rel="alternate" hreflang="x-default" href="{{ route('home') }}">

        <meta property="og:type" content="website">
        <meta property="og:site_name" content="{{ __('app.name') }}">
        <meta property="og:title" content="{{ $pageTitle }}">
        <meta property="og:description" content="{{ __('app.meta_description') }}">
        <meta property="og:url" content="{{ $canonical }}">
        <meta property="og:image" content="{{ asset('images/brand/og-image.png') }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:image:alt" content="{{ __('app.name') }} — {{ __('app.slogan') }}">
        <meta property="og:locale" content="{{ $locale->openGraph() }}">
        @foreach (\App\Enums\Locale::cases() as $alternate)
            @continue($alternate === $locale)
            <meta property="og:locale:alternate" content="{{ $alternate->openGraph() }}">
        @endforeach
        <meta name="twitter:card" content="summary_large_image">

        <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'EducationalOrganization',
                'name' => 'Éducœur',
                'alternateName' => 'إديكور',
                'slogan' => __('app.slogan'),
                'description' => __('app.meta_description'),
                'url' => route('home'),
                'logo' => asset('images/brand/logo-icon.svg'),
                'telephone' => $settings->get('contact.phone'),
                'email' => $settings->get('contact.email'),
                'geo' => [
                    '@type' => 'GeoCoordinates',
                    'latitude' => $settings->get('contact.latitude'),
                    'longitude' => $settings->get('contact.longitude'),
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
        </script>
    </x-slot:head>

    {{ $slot }}
</x-layouts.base>
