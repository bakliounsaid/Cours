{{--
    Carte d'une séance dans l'espace enseignant.
    $lesson, $live : affiche « en cours » / compte à rebours pour les séances du jour.
--}}
@php
    $roster = $lesson->group ? $lesson->group->students->merge($lesson->students)->unique('id') : $lesson->students;
@endphp

<article wire:key="lesson-{{ $lesson->id }}"
         style="--room: {{ $lesson->room->color }}"
         @if ($live)
             x-data="lessonClock({{ $lesson->starts_at->getTimestamp() }}, {{ $lesson->ends_at->getTimestamp() }})"
         @endif
         class="card card-hover mb-3 flex gap-4 overflow-hidden border-s-4 border-(--room) p-4">
    <div class="w-16 shrink-0 text-center" dir="ltr">
        <p class="font-display text-xl font-extrabold text-olive-700">{{ \App\Support\Format::time($lesson->starts_at) }}</p>
        <p class="text-xs font-semibold text-subtle">{{ \App\Support\Format::time($lesson->ends_at) }}</p>
        @unless ($live)
            <p class="mt-1 text-[11px] font-bold text-terra-700 capitalize">{{ $lesson->date->translatedFormat('D j') }}</p>
        @endunless
    </div>

    <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-center gap-1.5">
            <h3 class="truncate text-base font-bold text-ink">{{ $lesson->displayName() }}</h3>
            <span class="badge {{ $lesson->type->badgeClasses() }}">{{ $lesson->type->label() }}</span>
        </div>
        <p class="mt-0.5 truncate text-sm text-muted">{{ $lesson->subject->name }} · {{ __('admin.dashboard.room', ['name' => $lesson->room->name]) }}</p>
        <p class="mt-1 flex items-center gap-1 text-xs font-semibold text-subtle">
            <x-icon name="users" class="size-4" /> {{ trans_choice('teacher.students.count', $roster->count(), ['count' => $roster->count()]) }}
        </p>

        @if ($live)
            <p class="mt-2 text-xs font-bold" x-cloak>
                <span x-show="state === 'live'" class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-emerald-700">
                    <span class="relative flex size-2"><span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex size-2 rounded-full bg-emerald-500"></span></span>
                    {{ __('teacher.today.live') }}
                </span>
                <span x-show="state === 'soon'" class="inline-flex items-center gap-1 rounded-full bg-honey-100 px-2.5 py-1 text-honey-700">
                    <x-icon name="clock" class="size-3.5" /> <span x-text="@js(__('teacher.today.starts_in', ['time' => '__T__'])).replace('__T__', label)"></span>
                </span>
                <span x-show="state === 'done'" class="inline-flex items-center gap-1 rounded-full bg-sand-200 px-2.5 py-1 text-muted">
                    <x-icon name="check" class="size-3.5" /> {{ __('teacher.today.ended') }}
                </span>
            </p>
        @endif
    </div>

    @if ($lesson->group)
        <a href="{{ route('teacher.groups.show', $lesson->group) }}" wire:navigate class="self-center rounded-full p-2 text-subtle transition hover:bg-sand-200 hover:text-olive-700" title="{{ __('teacher.groups.open') }}">
            <x-icon name="chevron-right" class="flip-rtl" /><span class="sr-only">{{ __('teacher.groups.open') }}</span>
        </a>
    @endif
</article>
