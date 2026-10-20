<div class="space-y-5">
    <a href="{{ route('teacher.groups') }}" wire:navigate class="inline-flex items-center gap-1 text-sm font-semibold text-muted hover:text-terra-700">
        <x-icon name="chevron-left" class="flip-rtl size-4" /> {{ __('teacher.nav.groups') }}
    </a>

    {{-- En-tête du groupe --}}
    <section class="animate-fade-up card relative overflow-hidden p-5" style="--subject: {{ $group->subject->color }}">
        <span class="absolute inset-y-0 start-0 w-1.5 bg-(--subject)"></span>
        <div class="flex items-start gap-4">
            {{-- Jauge de remplissage --}}
            <div class="relative size-20 shrink-0" role="img" aria-label="{{ __('teacher.groups.fill', ['count' => $count, 'capacity' => $capacity]) }}">
                <svg viewBox="0 0 36 36" class="size-20 -rotate-90">
                    <circle cx="18" cy="18" r="15.5" fill="none" class="stroke-sand-200" stroke-width="3.5" />
                    <circle cx="18" cy="18" r="15.5" fill="none" stroke-width="3.5" stroke-linecap="round"
                            @class(['transition-[stroke-dasharray] duration-700', 'stroke-terra-500' => $full, 'stroke-sage-500' => ! $full])
                            stroke-dasharray="{{ $gauge }} 97.4" />
                </svg>
                <span class="absolute inset-0 flex flex-col items-center justify-center" dir="ltr">
                    <span class="font-display text-lg leading-none font-extrabold text-ink">{{ $count }}</span>
                    <span class="text-[10px] font-bold text-subtle">/ {{ $capacity }}</span>
                </span>
            </div>
            <div class="min-w-0 flex-1">
                <h1 class="text-2xl font-extrabold">{{ $group->name }}</h1>
                <p class="text-sm text-muted">{{ $group->subject->name }} · {{ $group->level->name }} · {{ __('admin.dashboard.room', ['name' => $group->room->name]) }}</p>
                <div class="mt-2 flex flex-wrap gap-1.5">
                    @foreach ($group->timeSlots as $slot)
                        <span class="badge bg-sand-200 text-olive-700 capitalize">{{ $slot->label() }}</span>
                    @endforeach
                    @unless ($group->is_active)
                        <span class="badge bg-sand-200 text-muted">{{ __('teacher.groups.archived_tab') }}</span>
                    @endunless
                </div>
            </div>
        </div>

        @if ($nextLessons->isNotEmpty())
            <div class="mt-4 border-t border-olive-100/70 pt-3">
                <p class="mb-2 text-xs font-bold tracking-wide text-subtle uppercase">{{ __('teacher.groups.next_lessons') }}</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($nextLessons as $lesson)
                        <a href="{{ route('teacher.lessons.show', $lesson) }}" wire:navigate class="inline-flex items-center gap-1.5 rounded-full bg-olive-50 px-3 py-1 text-xs font-semibold text-olive-700 transition hover:bg-olive-100">
                            <x-icon name="calendar" class="size-3.5" />
                            <span class="capitalize">{{ $lesson->date->translatedFormat('D j M') }}</span> · <span dir="ltr">{{ $lesson->starts_at->format('H:i') }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </section>

    {{-- Ajouter un élève --}}
    <section class="animate-fade-up card p-4 [animation-delay:60ms]" aria-labelledby="add-student">
        <div class="flex items-center justify-between gap-2">
            <h2 id="add-student" class="text-lg font-bold">{{ __('teacher.groups.add_students') }}</h2>
            @if ($full)
                <span class="badge bg-terra-50 text-terra-700">{{ __('teacher.groups.full_short') }}</span>
            @endif
        </div>

        @unless ($full)
            <div class="mt-3 flex gap-2">
                <label class="relative block flex-1">
                    <span class="sr-only">{{ __('teacher.groups.search_student') }}</span>
                    <x-icon name="search" class="pointer-events-none absolute start-3.5 top-1/2 size-5 -translate-y-1/2 text-subtle" />
                    <input type="search" wire:model.live.debounce.300ms="studentSearch" placeholder="{{ __('teacher.groups.search_student') }}" class="input ps-11" autocomplete="off">
                </label>
                <button type="button" wire:click="create" class="btn-secondary shrink-0" title="{{ __('teacher.students.add') }}">
                    <x-icon name="plus" class="size-4" /> <span class="hidden sm:inline">{{ __('teacher.students.new') }}</span>
                </button>
            </div>

            @if ($showSearchResults)
                <ul class="mt-2 divide-y divide-olive-50 overflow-hidden rounded-2xl ring-1 ring-olive-100" wire:loading.class="opacity-60" wire:target="studentSearch">
                    @forelse ($candidates as $candidate)
                        <li wire:key="candidate-{{ $candidate->id }}" class="flex items-center gap-3 bg-white px-3 py-2.5 transition hover:bg-sand-50">
                            <span class="avatar">{{ $candidate->initials() }}</span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate font-bold text-ink">{{ $candidate->full_name }}</span>
                                <span @class(['block truncate text-xs', 'font-semibold text-sage-700' => $candidate->level_id === $group->level_id, 'text-subtle' => $candidate->level_id !== $group->level_id])>{{ $candidate->level->name }}</span>
                            </span>
                            <button type="button" wire:click="addStudent({{ $candidate->id }})" class="btn-primary btn-sm">
                                <x-icon name="plus" class="size-3.5" /> {{ __('ui.add') }}
                            </button>
                        </li>
                    @empty
                        <li class="bg-white px-4 py-4 text-center text-sm text-subtle">
                            {{ __('teacher.groups.no_candidate') }}
                            <button type="button" wire:click="create" class="font-bold text-terra-700 hover:underline">{{ __('teacher.students.add') }}</button>
                        </li>
                    @endforelse
                </ul>
            @endif
        @endunless
    </section>

    {{-- Élèves du groupe --}}
    <section class="animate-fade-up [animation-delay:120ms]" aria-labelledby="roster">
        <h2 id="roster" class="mb-3 text-lg font-bold">{{ trans_choice('teacher.students.count', $count, ['count' => $count]) }}</h2>

        <ul class="card divide-y divide-olive-50 overflow-hidden">
            @forelse ($students as $student)
                <li wire:key="student-{{ $student->id }}" class="flex items-center gap-3 px-4 py-3 transition hover:bg-sand-50">
                    <span class="avatar">{{ $student->initials() }}</span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate font-bold text-ink">{{ $student->full_name }}</span>
                        <span class="block truncate text-xs text-subtle">
                            {{ $student->level->name }} · <span dir="ltr">{{ $student->parentPhoneDisplay() }}</span>
                        </span>
                        @if ($balances[$student->id]->alert())
                            <span class="badge mt-1 {{ $balances[$student->id]->status()->badgeClasses() }}"><x-icon name="banknotes" class="size-3.5" /> {{ $balances[$student->id]->alert() }}</span>
                        @endif
                    </span>
                    <a href="{{ $student->parentPhoneHref() }}" class="hidden size-9 items-center justify-center rounded-full text-muted transition hover:bg-sand-200 hover:text-ink sm:inline-flex" title="{{ __('teacher.students.call_parent') }}">
                        <x-icon name="phone" class="size-[18px]" /><span class="sr-only">{{ __('teacher.students.call_parent') }}</span>
                    </a>
                    <x-ui.icon-button icon="pencil" :label="__('ui.edit')" wire:click="edit({{ $student->id }})" />
                    <x-ui.icon-button icon="x" :label="__('teacher.groups.remove_student')" danger
                        x-on:click="$dispatch('confirm', { title: {{ Js::from(__('teacher.groups.remove_title')) }}, message: {{ Js::from(__('teacher.groups.remove_message', ['name' => $student->full_name, 'group' => $group->name])) }}, confirmLabel: {{ Js::from(__('teacher.groups.remove_student')) }}, onConfirm: () => $wire.removeStudent({{ $student->id }}) })" />
                </li>
            @empty
                <li class="flex flex-col items-center gap-2 p-10 text-center">
                    <span class="flex size-14 items-center justify-center rounded-full bg-sand-200 text-subtle"><x-icon name="users" class="size-7" /></span>
                    <p class="font-bold text-olive-700">{{ __('teacher.groups.no_students') }}</p>
                    <p class="text-sm text-subtle">{{ __('teacher.groups.no_students_hint') }}</p>
                </li>
            @endforelse
        </ul>
    </section>

    {{-- Historique des séances --}}
    <section class="animate-fade-up [animation-delay:180ms]" aria-labelledby="lessons-history">
        <h2 id="lessons-history" class="mb-3 text-lg font-bold">{{ __('teacher.lessons.group_history') }}</h2>

        <ul class="card divide-y divide-olive-50 overflow-hidden">
            @forelse ($history as $item)
                <li>
                    <a @if ($item['url']) href="{{ $item['url'] }}" wire:navigate @endif
                       @class(['flex items-start gap-3 px-4 py-3', 'transition hover:bg-sand-50' => $item['url']])>
                        <span class="min-w-0 flex-1">
                            <span class="flex flex-wrap items-center gap-2">
                                <span class="font-bold text-ink first-letter:uppercase">{{ $item['date'] }}</span>
                                <span class="text-xs text-subtle" dir="ltr">{{ $item['time'] }}</span>
                                <span class="badge {{ $item['statusClasses'] }}">{{ $item['status'] }}</span>
                            </span>
                            @if ($item['presence'])
                                <span class="mt-0.5 block text-xs font-semibold text-sage-700">{{ $item['presence'] }}</span>
                            @endif
                            <span class="mt-1 block text-sm text-muted">{{ $item['summary'] ?? __('teacher.lessons.no_summary') }}</span>
                        </span>
                        @if ($item['url'])
                            <x-icon name="chevron-right" class="flip-rtl mt-1 size-4 text-subtle" />
                        @endif
                    </a>
                </li>
            @empty
                <li class="p-6 text-center text-sm text-subtle">{{ __('teacher.lessons.no_past_lessons') }}</li>
            @endforelse
        </ul>
    </section>

    @include('livewire.teacher.partials.student-form', ['groupOptions' => null])
</div>
