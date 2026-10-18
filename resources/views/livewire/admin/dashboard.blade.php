@php use App\Support\Format; @endphp

<div>
    <x-ui.page-header :title="__('admin.nav.dashboard')" :description="Format::dayDate(now(), withYear: true)" />

    {{-- Chiffres clés --}}
    <div class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card :label="__('admin.dashboard.today_lessons')" :value="$stats['today']" icon="calendar-days" tone="navy" />
        <x-ui.stat-card :label="__('admin.dashboard.active_teachers')" :value="$stats['teachers']" icon="academic-cap" tone="green"
                        :href="route('admin.teachers')" />
        <x-ui.stat-card :label="__('admin.dashboard.active_students')" :value="$stats['students']" icon="users" tone="sun" />
        <x-ui.stat-card :label="__('admin.dashboard.new_enrollments')" :value="$stats['enrollments']" icon="inbox" tone="coral" />
    </div>

    @if ($stats['needs_action'] > 0)
        <div class="mb-6 flex items-center gap-3 rounded-2xl bg-terra-50 p-4 text-sm font-semibold text-terra-700 ring-1 ring-terra-200" role="status">
            <x-icon name="warning" /> {{ trans_choice('admin.dashboard.needs_action', $stats['needs_action'], ['count' => $stats['needs_action']]) }}
        </div>
    @endif

    {{-- Planning de la semaine --}}
    <section class="card overflow-hidden" aria-labelledby="planning-semaine">
        <div class="flex flex-col gap-3 border-b border-olive-100/70 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
            <div>
                <h2 id="planning-semaine" class="text-xl font-bold">{{ __('admin.dashboard.week_planning') }}</h2>
                <p class="text-sm text-muted">
                    {{ __('admin.dashboard.week_range', ['from' => Format::longDate($weekStart), 'to' => Format::longDate($weekEnd)]) }}
                    · {{ trans_choice('admin.dashboard.lessons_count', $weekCount, ['count' => $weekCount]) }}
                </p>
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

        {{-- Légende des salles --}}
        <div class="flex flex-wrap items-center gap-4 border-b border-olive-100/70 px-5 py-2.5 text-xs font-semibold text-muted">
            @foreach ($rooms as $room)
                <span class="inline-flex items-center gap-1.5"><span class="size-2.5 rounded-full" style="background: {{ $room->color }}"></span>{{ __('admin.dashboard.room', ['name' => $room->name]) }}</span>
            @endforeach
        </div>

        <div wire:loading.class="opacity-60" class="overflow-x-auto transition-opacity">
            <div class="grid min-w-[980px] grid-cols-7 divide-x divide-olive-100/70 rtl:divide-x-reverse">
                @foreach ($days as $day)
                    @php($isToday = $day['date']->isToday())
                    <div wire:key="day-{{ $day['date']->toDateString() }}" @class(['flex min-h-72 flex-col', 'bg-terra-50/50' => $isToday])>
                        <div @class(['sticky top-0 border-b border-olive-100/70 px-3 py-2.5 text-center', 'bg-terra-50' => $isToday, 'bg-sand-50' => ! $isToday])>
                            <p @class(['text-sm font-bold capitalize', 'text-terra-700' => $isToday, 'text-olive-700' => ! $isToday])>{{ $day['date']->translatedFormat('l') }}</p>
                            <p class="text-xs text-subtle">{{ $day['date']->translatedFormat('j F') }}</p>
                        </div>

                        <ul class="flex-1 space-y-2 p-2">
                            @forelse ($day['lessons'] as $lesson)
                                <li wire:key="lesson-{{ $lesson->id }}"
                                    style="--room: {{ $lesson->room->color }}"
                                    @class([
                                        'rounded-xl border-s-4 border-(--room) bg-white p-2.5 text-xs shadow-sm ring-1 ring-olive-100',
                                        'opacity-60' => $lesson->isCancelled(),
                                    ])>
                                    <p class="flex items-center justify-between gap-1 font-bold text-olive-700" dir="ltr">
                                        <span>{{ Format::timeRange($lesson->starts_at, $lesson->ends_at) }}</span>
                                    </p>
                                    <p @class(['mt-1 truncate font-bold text-ink', 'line-through' => $lesson->isCancelled()])>{{ $lesson->displayName() }}</p>
                                    <p class="truncate text-muted">{{ $lesson->subject->name }} · {{ $lesson->room->name }}</p>
                                    <p class="mt-1.5 flex items-center gap-1 font-semibold text-olive-700">
                                        <x-icon name="academic-cap" class="size-3.5" />
                                        <a href="{{ route('admin.teachers.show', $lesson->teacher) }}" wire:navigate class="truncate hover:text-terra-700 hover:underline">{{ $lesson->teacher->user->name }}</a>
                                    </p>
                                    @if ($lesson->originalTeacher)
                                        <p class="truncate text-[11px] text-subtle">{{ __('admin.lessons.replacing', ['name' => $lesson->originalTeacher->user->name]) }}</p>
                                    @endif
                                    @if ($lesson->status !== \App\Enums\LessonStatus::Scheduled || $lesson->type !== \App\Enums\LessonType::Group)
                                        <div class="mt-1.5 flex flex-wrap gap-1">
                                            @if ($lesson->type !== \App\Enums\LessonType::Group)
                                                <span class="badge {{ $lesson->type->badgeClasses() }} px-1.5 text-[10px]">{{ $lesson->type->label() }}</span>
                                            @endif
                                            @if ($lesson->status !== \App\Enums\LessonStatus::Scheduled)
                                                <span class="badge {{ $lesson->status->badgeClasses() }} px-1.5 text-[10px]">{{ $lesson->status->label() }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </li>
                            @empty
                                <li class="px-2 py-6 text-center text-xs text-subtle">{{ __('admin.dashboard.no_lessons') }}</li>
                            @endforelse
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</div>
