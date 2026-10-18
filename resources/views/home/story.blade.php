{{-- Notre histoire : mise en page éditoriale, grand « 2019 » en accent, frise et missions --}}
<section id="histoire" class="relative scroll-mt-20 overflow-hidden bg-sage-100 py-20 sm:py-28" aria-labelledby="histoire-titre">
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
                    <p class="text-sm text-muted">{{ __('home.story.timeline.today_text', ['rooms' => $rooms->count(), 'subjects' => $subjects->count()]) }}</p>
                </div>
            </li>
        </ol>

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
