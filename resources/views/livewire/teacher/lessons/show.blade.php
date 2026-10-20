<div class="space-y-5">
    <a href="{{ route('teacher.today') }}" wire:navigate class="inline-flex items-center gap-1 text-sm font-semibold text-muted hover:text-terra-700">
        <x-icon name="chevron-left" class="flip-rtl size-4" /> {{ __('teacher.nav.today') }}
    </a>

    {{-- En-tête de la séance --}}
    <section class="animate-fade-up card relative overflow-hidden p-5" style="--room: {{ $lesson->room->color }}">
        <span class="absolute inset-y-0 start-0 w-1.5 bg-(--room)"></span>
        <div class="flex flex-wrap items-center gap-2">
            <h1 class="text-2xl font-extrabold">{{ $title }}</h1>
            <span class="badge {{ $lesson->type->badgeClasses() }}">{{ $lesson->type->label() }}</span>
            <span class="badge {{ $lesson->status->badgeClasses() }}">{{ $lesson->status->label() }}</span>
        </div>
        <p class="mt-1 text-sm font-semibold text-olive-700 first-letter:uppercase">{{ $when }}</p>
        <p class="text-sm text-muted">{{ $place }}</p>

        <p class="mt-4 flex items-center gap-2 rounded-2xl px-4 py-3 text-sm font-bold ring-1 {{ $banner['classes'] }}" role="status">
            <x-icon :name="$banner['icon']" /> {{ $banner['text'] }}
        </p>
    </section>

    {{-- Feuille d'appel : saisie instantanée dans le navigateur, envoyée à la validation --}}
    <section class="animate-fade-up [animation-delay:60ms]" aria-labelledby="roll-call"
             x-data="{
                 marks: $wire.entangle('marks'),
                 count(status) { return Object.values(this.marks).filter(value => value === status).length },
                 all(status) { Object.keys(this.marks).forEach(id => this.marks[id] = status) },
             }">
        <div class="mb-3 flex flex-wrap items-end justify-between gap-2">
            <div>
                <h2 id="roll-call" class="text-lg font-bold">{{ __('teacher.lessons.roll_call') }}</h2>
                <p class="text-xs font-semibold text-subtle" dir="ltr">
                    @foreach ($statuses as $status)
                        <span class="me-2">{{ $status['label'] }} : <span x-text="count(@js($status['value']))"></span></span>
                    @endforeach
                </p>
            </div>
            @if ($canTake && $students->isNotEmpty())
                <button type="button" x-on:click="all('present')" class="btn-ghost btn-sm">
                    <x-icon name="check" class="size-4" /> {{ __('teacher.lessons.all_present') }}
                </button>
            @endif
        </div>

        <ul class="card divide-y divide-olive-50 overflow-hidden">
            @forelse ($students as $student)
                <li wire:key="mark-{{ $student['id'] }}" class="flex flex-wrap items-center gap-3 px-4 py-3">
                    <span class="avatar">{{ $student['initials'] }}</span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate font-bold text-ink">{{ $student['name'] }}</span>
                        <span class="block truncate text-xs text-subtle">{{ $student['level'] }} · {{ __('teacher.payments.sessions') }} <span dir="ltr">{{ $student['sessions'] }}</span></span>
                        @if ($student['alert'])
                            <span class="badge mt-1 {{ $student['alertClasses'] }}"><x-icon name="banknotes" class="size-3.5" /> {{ $student['alert'] }}</span>
                        @endif
                    </span>
                    <span class="flex gap-1" role="radiogroup" aria-label="{{ $student['name'] }}">
                        @foreach ($statuses as $status)
                            <button type="button" role="radio"
                                    @disabled(! $canTake)
                                    x-on:click="marks[{{ $student['id'] }}] = @js($status['value'])"
                                    :aria-checked="marks[{{ $student['id'] }}] === @js($status['value'])"
                                    :class="marks[{{ $student['id'] }}] === @js($status['value']) ? @js($status['selected']) : 'bg-white text-muted ring-olive-200 hover:bg-sand-100'"
                                    class="press rounded-full px-3 py-1.5 text-xs font-bold ring-1 transition disabled:cursor-not-allowed disabled:opacity-60">
                                {{ $status['label'] }}
                            </button>
                        @endforeach
                    </span>
                </li>
            @empty
                <li class="p-8 text-center text-sm text-subtle">{{ __('teacher.lessons.no_students') }}</li>
            @endforelse
        </ul>

        @if ($canTake && $students->isNotEmpty())
            <div class="sticky bottom-24 z-10 mt-3">
                <button type="button" wire:click="saveAttendance" wire:loading.attr="disabled" wire:target="saveAttendance" class="btn-primary w-full py-3 text-base shadow-lift">
                    <span wire:loading wire:target="saveAttendance" class="spinner"></span>
                    <x-icon name="clipboard-check" wire:loading.remove wire:target="saveAttendance" />
                    {{ $checkedIn ? __('teacher.lessons.update_attendance') : __('teacher.lessons.validate_attendance') }}
                </button>
            </div>
        @endif
    </section>

    {{-- Travail réalisé --}}
    <section class="animate-fade-up card p-5 [animation-delay:120ms]" aria-labelledby="work-done">
        <h2 id="work-done" class="flex items-center gap-2 text-lg font-bold"><x-icon name="book-open" class="text-terra-600" /> {{ __('teacher.lessons.work') }}</h2>
        <p class="mt-0.5 text-sm text-subtle">{{ __('teacher.lessons.work_hint') }}</p>
        <form wire:submit="saveWork" class="mt-4 space-y-4">
            <x-ui.textarea wire:model="summary" :label="__('teacher.lessons.summary')" rows="4" :placeholder="__('teacher.lessons.summary_placeholder')" />
            <x-ui.textarea wire:model="homework" :label="__('teacher.lessons.homework')" rows="2" :placeholder="__('teacher.lessons.homework_placeholder')" />
            <div class="flex justify-end">
                <button type="submit" class="btn-secondary" wire:loading.attr="disabled" wire:target="saveWork">
                    <span wire:loading wire:target="saveWork" class="spinner"></span> {{ __('ui.save') }}
                </button>
            </div>
        </form>
    </section>

    {{-- Journal de la séance --}}
    <section class="animate-fade-up [animation-delay:180ms]" aria-labelledby="history">
        <h2 id="history" class="mb-3 text-lg font-bold">{{ __('teacher.lessons.history') }}</h2>

        @if ($events->isEmpty())
            <p class="card p-6 text-center text-sm text-subtle">{{ __('teacher.lessons.no_history') }}</p>
        @else
            <ol class="relative space-y-4 border-s-2 border-olive-100 ps-6">
                @foreach ($events as $event)
                    <li class="relative">
                        <span class="absolute -start-[2.35rem] flex size-8 items-center justify-center rounded-full bg-white text-terra-600 ring-2 ring-olive-100">
                            <x-icon :name="$event['icon']" class="size-4" />
                        </span>
                        <div class="card p-4">
                            <p class="flex flex-wrap items-baseline justify-between gap-2">
                                <span class="font-bold text-ink">{{ $event['label'] }}</span>
                                <span class="text-xs text-subtle" dir="ltr">{{ $event['at'] }}</span>
                            </p>
                            <p class="text-xs font-semibold text-muted">{{ $event['by'] }}</p>
                            @foreach ($event['details'] as $detail)
                                <p class="mt-1 text-sm whitespace-pre-line text-olive-700">{{ $detail }}</p>
                            @endforeach
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif
    </section>
</div>
