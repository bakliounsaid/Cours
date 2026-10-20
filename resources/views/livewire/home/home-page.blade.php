<div>
    @include('home.header')

    <main id="contenu">
        @include('home.hero')
        @include('home.why')
        @include('home.story')
        @include('home.subjects')
        @include('home.levels-offers')
        @include('home.hours')
        @include('home.contact')
    </main>

    @include('home.footer')

    {{-- Bouton WhatsApp flottant (mobile) --}}
    <a href="{{ $contact['whatsapp_href'] }}" target="_blank" rel="noopener"
       class="fixed bottom-5 end-5 z-40 flex size-14 items-center justify-center rounded-full bg-[#0F7A41] text-white shadow-lift transition hover:scale-105 sm:hidden"
       aria-label="{{ __('home.whatsapp_cta') }}">
        <x-icon name="whatsapp" class="size-7" />
    </a>
</div>
