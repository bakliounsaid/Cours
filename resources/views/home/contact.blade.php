<section id="contact" class="relative scroll-mt-20 overflow-hidden bg-olive-800 py-20 text-white sm:py-24">
    <div aria-hidden="true" class="pointer-events-none absolute -top-24 -end-24 size-96 rounded-full bg-terra-600/20 blur-3xl"></div>

    <div class="relative mx-auto grid max-w-6xl gap-10 px-4 sm:px-6 lg:grid-cols-[1fr_1.15fr]">
        <div>
            <p x-reveal class="text-sm font-extrabold tracking-wider text-terra-200 uppercase">{{ __('home.contact.eyebrow') }}</p>
            <h2 x-reveal="80" class="mt-3 text-3xl font-extrabold text-white sm:text-4xl">{{ __('home.contact.title') }}</h2>
            <p x-reveal="160" class="mt-4 text-lg text-olive-100">{{ __('home.contact.subtitle') }}</p>

            <ul x-reveal="200" class="mt-8 space-y-4">
                <li class="flex items-start gap-4">
                    <span class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-olive-700 text-terra-200"><x-icon name="map-pin" /></span>
                    <span><span class="block text-sm text-olive-200">{{ __('home.contact.address') }}</span><span class="font-bold">{{ $contact['address'] }}</span></span>
                </li>
                <li class="flex items-start gap-4">
                    <span class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-olive-700 text-terra-200"><x-icon name="phone" /></span>
                    <span><span class="block text-sm text-olive-200">{{ __('home.contact.phone') }}</span><a href="{{ $contact['phone_href'] }}" class="font-bold hover:text-terra-200" dir="ltr">{{ $contact['phone'] }}</a></span>
                </li>
                <li class="flex items-start gap-4">
                    <span class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-olive-700 text-[#25D366]"><x-icon name="whatsapp" /></span>
                    <span><span class="block text-sm text-olive-200">{{ __('home.contact.whatsapp') }}</span><a href="{{ $contact['whatsapp_href'] }}" target="_blank" rel="noopener" class="font-bold hover:text-terra-200">{{ __('home.whatsapp_cta') }}</a></span>
                </li>
            </ul>

            {{-- Carte OpenStreetMap (Leaflet), chargée à l'apparition --}}
            <div x-reveal="260" class="mt-8 overflow-hidden rounded-card ring-4 ring-olive-700">
                <div wire:ignore
                     x-data
                     x-intersect.once="window.educoeurMap?.($el, {{ $contact['latitude'] }}, {{ $contact['longitude'] }}, {{ Js::from($contact['address']) }})"
                     class="h-64 w-full bg-olive-700"
                     role="img"
                     aria-label="{{ __('home.contact.map_title') }}"></div>
            </div>
            <a href="https://www.openstreetmap.org/?mlat={{ $contact['latitude'] }}&mlon={{ $contact['longitude'] }}#map=16/{{ $contact['latitude'] }}/{{ $contact['longitude'] }}"
               target="_blank" rel="noopener" class="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-olive-100 hover:text-white">
                {{ __('home.contact.open_map') }} <x-icon name="arrow-up-right" class="size-4 rtl:-scale-x-100" />
            </a>
        </div>

        <div x-reveal="120" class="rounded-card bg-white p-6 text-ink shadow-lift sm:p-8">
            <livewire:home.enrollment-form />
        </div>
    </div>
</section>
