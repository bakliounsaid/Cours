<section id="horaires" class="scroll-mt-20 py-20 sm:py-24">
    <div class="mx-auto max-w-3xl px-4 sm:px-6">
        <div class="text-center">
            <p x-reveal class="text-sm font-extrabold tracking-wider text-terra-700 uppercase">{{ __('home.hours.eyebrow') }}</p>
            <h2 x-reveal="80" class="mt-3 text-3xl font-extrabold sm:text-4xl">{{ __('home.hours.title') }}</h2>
            <p x-reveal="160" class="mt-4 text-lg text-muted">{{ __('home.hours.subtitle') }}</p>
        </div>

        <ul x-reveal="200" class="card mt-8 divide-y divide-olive-50 overflow-hidden">
            @foreach ($openingHours as $row)
                <li class="flex flex-col gap-2 p-5 transition hover:bg-sand-50 sm:flex-row sm:items-center sm:justify-between">
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
</section>
