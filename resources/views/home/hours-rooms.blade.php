<section id="horaires" class="scroll-mt-20 py-20 sm:py-24">
    <div class="mx-auto grid max-w-6xl gap-12 px-4 sm:px-6 lg:grid-cols-2 lg:items-start">
        <div>
            <div class="text-center lg:text-start">
                <p x-reveal class="text-sm font-extrabold tracking-wider text-terra-700 uppercase">{{ __('home.hours.eyebrow') }}</p>
                <h2 x-reveal="80" class="mt-3 text-3xl font-extrabold sm:text-4xl">{{ __('home.hours.title') }}</h2>
                <p x-reveal="160" class="mt-4 text-lg text-muted">{{ __('home.hours.subtitle') }}</p>
            </div>

            <ul x-reveal="200" class="card mt-8 divide-y divide-olive-50 overflow-hidden">
                @foreach ($openingHours as $row)
                    <li class="flex flex-col gap-2 p-5 sm:flex-row sm:items-center sm:justify-between">
                        <span class="flex items-center gap-3 font-bold text-ink">
                            <span class="flex size-10 items-center justify-center rounded-xl bg-terra-50 text-terra-700"><x-icon name="clock" /></span>
                            {{ $row['days'] }}
                        </span>
                        <span class="flex flex-wrap items-center gap-2 sm:justify-end">
                            @foreach ($row['ranges'] as $range)
                                <span class="rounded-full bg-olive-700 px-3 py-1 text-sm font-bold text-white" dir="ltr">{{ $range }}</span>
                            @endforeach
                            @if ($row['short_break'])
                                <span class="text-xs font-semibold text-subtle">({{ __('home.hours.short_break') }})</span>
                            @endif
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>

        <div>
            <div class="text-center lg:text-start">
                <p x-reveal class="text-sm font-extrabold tracking-wider text-terra-700 uppercase">{{ __('home.rooms.eyebrow') }}</p>
                <h2 x-reveal="80" class="mt-3 text-3xl font-extrabold sm:text-4xl">{{ __('home.rooms.title') }}</h2>
                <p x-reveal="160" class="mt-4 text-lg text-muted">{{ __('home.rooms.subtitle') }}</p>
            </div>

            <div class="mt-8 grid gap-5 sm:grid-cols-2">
                @foreach ($rooms as $room)
                    <article x-reveal="{{ 200 + $loop->index * 100 }}" class="overflow-hidden rounded-card bg-white shadow-soft ring-1 ring-olive-100/60">
                        {{-- Illustration douce d'une salle lumineuse --}}
                        <div class="relative h-36 overflow-hidden" style="background: linear-gradient(160deg, {{ $room->color }}22, #FAF6EE)" aria-hidden="true">
                            <div class="absolute start-6 top-5 h-16 w-24 rounded-xl border-4 border-white/90 bg-sky-100/80 shadow-sm">
                                <div class="absolute inset-y-0 start-1/2 w-1 -translate-x-1/2 bg-white/90"></div>
                            </div>
                            <div class="absolute -end-4 -top-4 size-16 rounded-full bg-honey-300/70 blur-sm"></div>
                            <div class="absolute inset-x-6 bottom-4 flex items-end justify-between">
                                @foreach (range(1, 4) as $desk)
                                    <div class="h-5 w-10 rounded-t-lg" style="background: {{ $room->color }}"></div>
                                @endforeach
                            </div>
                        </div>
                        <div class="p-5">
                            <h3 class="text-lg font-bold">{{ __('home.rooms.room_name', ['name' => $room->name]) }}</h3>
                            <p class="mt-1 text-sm text-muted">{{ __('home.rooms.room_text', ['capacity' => $room->capacity]) }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
