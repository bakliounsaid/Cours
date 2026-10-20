<div>
    <div class="animate-fade-up mb-5">
        <h1 class="text-2xl font-extrabold sm:text-3xl">{{ __('teacher.nav.lessons') }}</h1>
        <p class="text-sm text-muted">{{ __('teacher.lessons_list.description') }}</p>
    </div>

    <div class="animate-fade-up mb-5 flex flex-col gap-3 [animation-delay:60ms] sm:flex-row sm:items-center">
        <div class="segmented flex-1" role="tablist">
            @foreach ($tabs as $value => $item)
                <button type="button" role="tab" wire:click="$set('tab', '{{ $value }}')" aria-selected="{{ $tab === $value ? 'true' : 'false' }}">
                    {{ $item['label'] }} <span class="rounded-full bg-sand-200 px-1.5 text-[11px] text-muted">{{ $item['count'] }}</span>
                </button>
            @endforeach
        </div>
        <label class="sm:w-60">
            <span class="sr-only">{{ __('teacher.lessons_list.group_filter') }}</span>
            <select wire:model.live="group" class="input py-2 text-sm">
                <option value="">{{ __('teacher.lessons_list.all_groups') }}</option>
                @foreach ($groupOptions as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </label>
    </div>

    <div wire:loading.class="opacity-60" wire:target="tab,group,gotoPage,nextPage,previousPage" class="space-y-6 transition-opacity">
        @forelse ($days as $day)
            <section wire:key="day-{{ $loop->index }}-{{ $day['date'] }}" class="animate-fade-up">
                <h2 @class(['mb-2 text-sm font-extrabold tracking-wide uppercase first-letter:uppercase', 'text-terra-700' => $day['isToday'], 'text-muted' => ! $day['isToday']])>
                    {{ $day['date'] }}
                </h2>
                <ul class="card divide-y divide-olive-50 overflow-hidden">
                    @foreach ($day['lessons'] as $lesson)
                        <li wire:key="lesson-{{ $lesson['id'] }}">
                            <a href="{{ $lesson['url'] }}" wire:navigate style="--room: {{ $lesson['color'] }}"
                               class="flex items-center gap-4 border-s-4 border-(--room) px-4 py-3 transition hover:bg-sand-50">
                                <span class="w-28 shrink-0 font-display font-extrabold text-olive-700" dir="ltr">{{ $lesson['time'] }}</span>
                                <span class="min-w-0 flex-1">
                                    <span class="flex flex-wrap items-center gap-1.5">
                                        <span @class(['truncate font-bold text-ink', 'line-through' => $lesson['cancelled']])>{{ $lesson['name'] }}</span>
                                        <span class="badge {{ $lesson['typeClasses'] }}">{{ $lesson['type'] }}</span>
                                        <span class="badge {{ $lesson['statusClasses'] }}">{{ $lesson['status'] }}</span>
                                    </span>
                                    <span class="block truncate text-sm text-muted">{{ $lesson['place'] }}</span>
                                    @if ($lesson['note'])
                                        <span class="block truncate text-xs font-semibold text-subtle">{{ $lesson['note'] }}</span>
                                    @endif
                                </span>
                                <x-icon name="chevron-right" class="flip-rtl size-5 shrink-0 text-subtle" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @empty
            <div class="card flex flex-col items-center gap-2 p-10 text-center">
                <span class="flex size-14 items-center justify-center rounded-full bg-sand-200 text-subtle"><x-icon name="calendar" class="size-7" /></span>
                <p class="font-bold text-olive-700">{{ __('teacher.lessons_list.empty') }}</p>
            </div>
        @endforelse
    </div>

    @if ($lessons->hasPages())
        <div class="mt-6 flex justify-center">{{ $lessons->onEachSide(1)->links('tables.pagination') }}</div>
    @endif
</div>
