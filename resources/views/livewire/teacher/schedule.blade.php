<div>
    <div class="animate-fade-up mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold sm:text-3xl">{{ __('teacher.nav.schedule') }}</h1>
            <p class="text-sm text-muted">{{ $range }} · {{ $countLabel }}</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" wire:click="previousWeek" class="btn-ghost btn-sm" title="{{ __('ui.previous_week') }}">
                <x-icon name="chevron-left" class="flip-rtl size-4" /><span class="sr-only sm:not-sr-only">{{ __('ui.previous_week') }}</span>
            </button>
            @unless ($isCurrentWeek)
                <button type="button" wire:click="currentWeek" class="btn-secondary btn-sm">{{ __('ui.this_week') }}</button>
            @endunless
            <button type="button" wire:click="nextWeek" class="btn-ghost btn-sm" title="{{ __('ui.next_week') }}">
                <span class="sr-only sm:not-sr-only">{{ __('ui.next_week') }}</span><x-icon name="chevron-right" class="flip-rtl size-4" />
            </button>
        </div>
    </div>

    <section class="animate-fade-up card overflow-hidden [animation-delay:60ms]" wire:loading.class="opacity-60">
        {{-- Mobile et tablette : jours empilés ; grand écran : 7 colonnes --}}
        <div class="grid divide-y divide-olive-100/70 lg:grid-cols-7 lg:divide-x lg:divide-y-0 lg:rtl:divide-x-reverse">
            @foreach ($days as $day)
                <div wire:key="day-{{ $day['key'] }}" @class(['flex flex-col lg:min-h-80', 'bg-terra-50/50' => $day['isToday']])>
                    <div @class(['flex items-baseline justify-between gap-2 border-b border-olive-100/70 px-4 py-2.5 lg:block lg:px-2 lg:text-center', 'bg-terra-50' => $day['isToday'], 'bg-sand-50' => ! $day['isToday']])>
                        <p @class(['text-sm font-bold capitalize', 'text-terra-700' => $day['isToday'], 'text-olive-700' => ! $day['isToday']])>{{ $day['name'] }}</p>
                        <p class="text-xs text-subtle">{{ $day['date'] }}</p>
                    </div>

                    <ul class="grid flex-1 gap-2 p-2 sm:grid-cols-2 lg:grid-cols-1 lg:content-start">
                        @forelse ($day['lessons'] as $lesson)
                            <li wire:key="lesson-{{ $lesson['id'] }}">
                                <a href="{{ $lesson['url'] }}" wire:navigate style="--room: {{ $lesson['color'] }}"
                                   @class(['card-hover block rounded-xl border-s-4 border-(--room) bg-white p-2.5 text-xs shadow-sm ring-1 ring-olive-100', 'opacity-60' => $lesson['cancelled']])>
                                    <p class="font-bold text-olive-700" dir="ltr">{{ $lesson['time'] }}</p>
                                    <p @class(['mt-1 truncate font-bold text-ink', 'line-through' => $lesson['cancelled']])>{{ $lesson['name'] }}</p>
                                    <p class="truncate text-muted">{{ $lesson['place'] }}</p>
                                    @if ($lesson['badge'])
                                        <span class="badge mt-1.5 px-1.5 text-[10px] {{ $lesson['badgeClasses'] }}">{{ $lesson['badge'] }}</span>
                                    @endif
                                </a>
                            </li>
                        @empty
                            <li class="px-2 py-3 text-center text-xs text-subtle lg:py-6">{{ __('admin.dashboard.no_lessons') }}</li>
                        @endforelse
                    </ul>
                </div>
            @endforeach
        </div>
    </section>
</div>
