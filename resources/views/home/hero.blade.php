<section class="relative overflow-hidden pt-28 pb-20 sm:pt-36 lg:pb-28">
    {{-- Formes douces --}}
    <div aria-hidden="true" class="pointer-events-none absolute -top-32 -end-32 size-[32rem] rounded-full bg-terra-100/70 blur-3xl"></div>
    <div aria-hidden="true" class="pointer-events-none absolute top-1/2 -start-40 size-96 rounded-full bg-honey-200/50 blur-3xl"></div>

    <div class="relative mx-auto grid max-w-6xl items-center gap-14 px-4 sm:px-6 lg:grid-cols-[1.1fr_1fr]">
        <div>
            <p x-reveal class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-1.5 text-sm font-bold text-muted shadow-soft ring-1 ring-olive-100">
                <x-icon name="heart" class="size-4 text-terra-700" /> {{ $heroBadge }}
            </p>

            <h1 x-reveal="80" class="mt-6 text-4xl leading-[1.1] font-extrabold text-balance sm:text-5xl lg:text-6xl">
                {{ $heroTitle }}
            </h1>

            <p x-reveal="160" class="mt-3 font-display text-xl font-bold text-terra-700 sm:text-2xl">{{ __('app.slogan') }}</p>

            <p x-reveal="240" class="mt-5 max-w-xl text-lg leading-relaxed text-muted">{{ $heroTagline }}</p>

            <div x-reveal="320" class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="#contact" class="btn-primary px-7 py-3.5 text-base">
                    <x-icon name="heart" class="size-5" /> {{ __('home.enroll_cta') }}
                </a>
                <a href="{{ $contact['whatsapp_href'] }}" target="_blank" rel="noopener" class="btn bg-[#0F7A41] px-7 py-3.5 text-base text-white shadow-soft hover:bg-[#0C6636]">
                    <x-icon name="whatsapp" /> {{ __('home.whatsapp_cta') }}
                </a>
            </div>

            <ul x-reveal="400" class="mt-8 flex flex-wrap gap-2.5">
                @foreach ($heroChips as $chip)
                    <li class="inline-flex items-center gap-2 rounded-full bg-white/80 px-3.5 py-1.5 text-sm font-semibold text-muted ring-1 ring-olive-100">
                        <x-icon :name="$chip['icon']" class="size-4 text-subtle" /> {{ $chip['label'] }}
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Photo mise en avant : notre nouvelle salle fraîchement repeinte --}}
        <div x-reveal="200" class="relative mx-auto w-full max-w-md">
            <div aria-hidden="true" class="absolute -inset-4 rotate-3 rounded-[2.5rem] bg-gradient-to-br from-terra-200 via-honey-200 to-sage-200"></div>
            <figure class="group relative overflow-hidden rounded-[2rem] bg-white p-2 shadow-lift ring-1 ring-olive-100">
                <picture>
                    <source srcset="{{ $featuredPhoto['webp'] }}" type="image/webp">
                    <img src="{{ $featuredPhoto['jpg'] }}" width="{{ $featuredPhoto['width'] }}" height="{{ $featuredPhoto['height'] }}"
                         alt="{{ $featuredPhoto['caption'] }}" fetchpriority="high"
                         class="aspect-[4/5] w-full rounded-[1.6rem] object-cover transition duration-700 group-hover:scale-[1.03]">
                </picture>
                <figcaption class="absolute inset-x-2 bottom-2 rounded-b-[1.6rem] bg-gradient-to-t from-olive-900/80 to-transparent px-5 pt-12 pb-4 font-display text-lg font-bold text-white">
                    {{ $featuredPhoto['caption'] }}
                </figcaption>
            </figure>

            <div aria-hidden="true" class="absolute -start-6 top-10 flex animate-float items-center gap-2.5 rounded-2xl bg-white px-4 py-3 shadow-lift [animation-delay:-2s]">
                <span class="flex size-8 items-center justify-center rounded-full bg-emerald-100 text-emerald-600"><x-icon name="check" class="size-4" /></span>
                <span class="text-sm font-bold text-olive-700">{{ __('home.hero.card_attendance') }}</span>
            </div>
            <div aria-hidden="true" class="absolute -end-6 top-1/2 flex animate-float items-center gap-2.5 rounded-2xl bg-white px-4 py-3 shadow-lift [animation-delay:-4s]">
                <span class="flex size-8 items-center justify-center rounded-full bg-honey-100 text-honey-700"><x-icon name="star" class="size-4" /></span>
                <span class="text-sm font-bold text-olive-700">{{ __('home.hero.card_progress') }}</span>
            </div>
            <div aria-hidden="true" class="absolute -start-4 bottom-20 hidden animate-float items-center gap-2.5 rounded-2xl bg-white px-4 py-3 shadow-lift [animation-delay:-1s] sm:flex">
                <span class="flex size-8 items-center justify-center rounded-full bg-terra-100 text-terra-700"><x-icon name="chat" class="size-4" /></span>
                <span class="text-sm font-bold text-olive-700">{{ __('home.hero.card_parents') }}</span>
            </div>
        </div>
    </div>
</section>
