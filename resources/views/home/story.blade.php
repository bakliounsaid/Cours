{{-- Notre histoire : mise en page éditoriale, grand « 2019 » en accent, frise et missions --}}
<section id="histoire" class="relative scroll-mt-20 overflow-hidden bg-sage-100 py-20 sm:py-28" aria-labelledby="histoire-titre"
         x-data="{
             photos: @js($gallery),
             current: null,
             next() { this.current = (this.current + 1) % this.photos.length },
             previous() { this.current = (this.current - 1 + this.photos.length) % this.photos.length },
         }"
         x-on:keydown.escape.window="current = null"
         x-on:keydown.arrow-right.window="current !== null && next()"
         x-on:keydown.arrow-left.window="current !== null && previous()">
    <div aria-hidden="true" class="pointer-events-none absolute -end-24 top-10 size-80 rounded-full bg-sage-200/70 blur-2xl"></div>
    <div aria-hidden="true" class="pointer-events-none absolute -start-16 bottom-0 size-64 rounded-full bg-honey-200/50 blur-2xl"></div>

    <div class="relative mx-auto max-w-6xl px-4 sm:px-6">
        <div class="grid items-center gap-12 lg:grid-cols-[0.85fr_1.4fr] lg:gap-16">
            {{-- Grande date --}}
            <div x-reveal class="text-center lg:text-start">
                <p class="text-sm font-extrabold tracking-[0.2em] text-olive-700 uppercase">{{ __('home.story.founded') }}</p>
                <p class="font-['Baloo_2'] text-[7.5rem] leading-[0.9] font-extrabold tracking-tight text-terra-600 sm:text-[10rem]" dir="ltr">
                    {{ $story['year'] }}
                </p>
                <span aria-hidden="true" class="mx-auto mt-3 block h-2 w-28 rounded-full bg-honey-400 lg:mx-0"></span>
                <x-brand.mark aria-hidden="true" class="mx-auto mt-8 hidden size-24 opacity-90 lg:mx-0 lg:block" />
            </div>

            {{-- Récit --}}
            <div>
                <p x-reveal class="inline-flex items-center gap-2 text-sm font-extrabold tracking-wider text-terra-700 uppercase">
                    <span class="h-0.5 w-6 rounded-full bg-terra-500"></span>{{ __('home.story.eyebrow') }}
                </p>
                <h2 id="histoire-titre" x-reveal="80" class="mt-3 text-3xl font-extrabold text-balance sm:text-4xl">{{ __('home.story.title') }}</h2>

                <blockquote x-reveal="160" class="relative mt-6">
                    <span aria-hidden="true" class="absolute -start-2 -top-8 font-['Baloo_2'] text-8xl leading-none text-terra-500/30 rtl:-scale-x-100">“</span>
                    <p class="relative text-lg leading-relaxed text-ink sm:text-xl sm:leading-relaxed">{{ $story['text'] }}</p>
                    <footer class="mt-5 flex items-center gap-3">
                        <span aria-hidden="true" class="h-0.5 w-10 rounded-full bg-olive-700"></span>
                        <cite class="font-display text-lg font-bold text-olive-700 not-italic">{{ $story['founder'] }}</cite>
                    </footer>
                </blockquote>
            </div>
        </div>

        {{-- Frise --}}
        <ol x-reveal="120" class="relative mt-16 grid gap-6 sm:grid-cols-2" aria-label="{{ __('home.story.timeline_label') }}">
            <span aria-hidden="true" class="absolute inset-x-8 top-6 hidden border-t-2 border-dashed border-olive-300 sm:block"></span>
            <li class="relative flex items-start gap-4 sm:flex-col sm:items-center sm:text-center">
                <span class="relative flex size-12 shrink-0 items-center justify-center rounded-full bg-terra-600 font-display text-sm font-extrabold text-white shadow-soft ring-4 ring-sage-100" dir="ltr">{{ $story['year'] }}</span>
                <p class="font-bold text-olive-700 sm:mt-1">{{ __('home.story.timeline.founded') }}</p>
            </li>
            <li class="relative flex items-start gap-4 sm:flex-col sm:items-center sm:text-center">
                <span class="relative flex size-12 shrink-0 items-center justify-center rounded-full bg-olive-700 text-white shadow-soft ring-4 ring-sage-100">
                    <x-icon name="heart" class="size-6" />
                </span>
                <div class="sm:mt-1">
                    <p class="font-bold text-olive-700">{{ __('home.story.timeline.today') }}</p>
                    <p class="text-sm text-muted">{{ __('home.story.timeline.today_text') }}</p>
                </div>
            </li>
        </ol>

        {{-- Notre histoire en images : petites photos, agrandies au clic --}}
        <div class="mt-16">
            <h3 x-reveal class="text-center font-display text-xl font-bold text-olive-700">{{ __('home.gallery.title') }}</h3>
            <ul class="mt-6 grid grid-cols-3 gap-2.5 sm:grid-cols-6 sm:gap-3">
                @foreach ($gallery as $photo)
                    <li x-reveal="{{ $loop->index * 60 }}">
                        <button type="button" x-on:click="current = {{ $loop->index }}"
                                class="group relative block aspect-square w-full overflow-hidden rounded-2xl bg-sand-200 shadow-soft ring-2 ring-white transition hover:-translate-y-1 hover:shadow-lift focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-terra-500"
                                title="{{ $photo['caption'] }}">
                            <picture>
                                <source srcset="{{ $photo['webp'] }}" type="image/webp">
                                <img src="{{ $photo['jpg'] }}" width="{{ $photo['width'] }}" height="{{ $photo['height'] }}" alt="{{ $photo['caption'] }}"
                                     loading="lazy" decoding="async" class="size-full object-cover transition duration-500 group-hover:scale-110">
                            </picture>
                            <span class="sr-only">{{ __('home.gallery.open') }}</span>
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Visionneuse --}}
        <div x-show="current !== null" x-cloak x-transition.opacity
             class="fixed inset-0 z-[60] flex items-center justify-center bg-olive-950/90 p-4 backdrop-blur-sm"
             role="dialog" aria-modal="true" x-on:click.self="current = null">
            <template x-if="current !== null">
                <figure class="flex max-h-full w-full max-w-4xl flex-col items-center">
                    <img :src="photos[current].webp" :alt="photos[current].caption" class="max-h-[78dvh] w-auto rounded-2xl object-contain shadow-lift">
                    <figcaption class="mt-4 text-center font-display text-lg font-bold text-white" x-text="photos[current].caption"></figcaption>
                </figure>
            </template>
            <button type="button" x-on:click="current = null" class="absolute end-4 top-4 flex size-11 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20">
                <x-icon name="x" /><span class="sr-only">{{ __('home.gallery.close') }}</span>
            </button>
            <button type="button" x-on:click="previous()" class="absolute start-3 top-1/2 flex size-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20">
                <x-icon name="chevron-left" class="flip-rtl" /><span class="sr-only">{{ __('home.gallery.previous') }}</span>
            </button>
            <button type="button" x-on:click="next()" class="absolute end-3 top-1/2 flex size-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20">
                <x-icon name="chevron-right" class="flip-rtl" /><span class="sr-only">{{ __('home.gallery.next') }}</span>
            </button>
        </div>

        {{-- Missions --}}
        <h3 class="sr-only">{{ __('home.story.missions_title') }}</h3>
        <ul class="mt-14 grid gap-5 md:grid-cols-3">
            @foreach ($story['missions'] as $mission)
                <li x-reveal="{{ $loop->index * 100 }}" class="rounded-card bg-white p-6 shadow-soft ring-1 ring-sage-300/60">
                    <span class="flex size-12 items-center justify-center rounded-2xl bg-honey-100 text-honey-700">
                        <x-icon :name="$mission['icon']" class="size-6" />
                    </span>
                    <p class="mt-4 font-display text-lg font-bold text-olive-700">{{ $mission['title'] }}</p>
                    <p class="mt-1.5 text-muted">{{ $mission['text'] }}</p>
                </li>
            @endforeach
        </ul>
    </div>
</section>
