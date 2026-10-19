<div>
    <div class="animate-fade-up mb-5 flex items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold">{{ __('teacher.nav.students') }}</h1>
            <p class="text-sm text-muted">{{ __('teacher.students.description') }}</p>
        </div>
        <button type="button" wire:click="create" class="btn-primary shrink-0">
            <x-icon name="plus" class="size-4" /> <span class="hidden sm:inline">{{ __('teacher.students.add') }}</span><span class="sm:hidden">{{ __('ui.add') }}</span>
        </button>
    </div>

    <div class="animate-fade-up mb-4 space-y-3 [animation-delay:60ms]">
        <label class="relative block">
            <span class="sr-only">{{ __('ui.search') }}</span>
            <x-icon name="search" class="pointer-events-none absolute start-3.5 top-1/2 size-5 -translate-y-1/2 text-subtle" />
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="{{ __('teacher.students.search') }}" class="input ps-11">
        </label>
        <div class="segmented" role="tablist">
            @foreach (['all', 'group', 'individual'] as $value)
                <button type="button" role="tab" wire:click="$set('mode', '{{ $value }}')" aria-selected="{{ $mode === $value ? 'true' : 'false' }}">
                    {{ __('teacher.students.filters.'.$value) }}
                    <span class="rounded-full bg-sand-200 px-1.5 text-[11px] text-muted">{{ $counts[$value] }}</span>
                </button>
            @endforeach
        </div>
    </div>

    <ul wire:loading.class="opacity-60" wire:target="search,mode" class="space-y-3 transition-opacity">
        @forelse ($students as $student)
            <li wire:key="student-{{ $student->id }}" style="animation-delay: {{ $loop->index * 40 }}ms"
                class="animate-fade-up card card-hover p-4">
                <div class="flex items-start gap-3">
                    <span class="avatar size-11 text-base">{{ $student->initials() }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate font-bold text-ink">{{ $student->full_name }}</p>
                        <p class="truncate text-xs text-subtle">{{ $student->level->name }}</p>
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            @foreach ($student->groups as $group)
                                <a href="{{ route('teacher.groups.show', $group) }}" wire:navigate class="badge bg-olive-50 text-olive-700 transition hover:bg-olive-100">
                                    <x-icon name="user-group" class="size-3.5" /> {{ $group->name }}
                                </a>
                            @endforeach
                            @if ($student->is_individual)
                                <span class="badge bg-honey-100 text-honey-700"><x-icon name="user" class="size-3.5" /> {{ __('teacher.students.individual_badge') }}</span>
                            @endif
                        </div>
                        @if ($student->nextLesson)
                            <p class="mt-2 flex items-center gap-1 text-xs font-semibold text-terra-700">
                                <x-icon name="calendar" class="size-3.5" />
                                {{ __('teacher.students.next_lesson') }} <span class="capitalize">{{ $student->nextLesson->date->translatedFormat('l j F') }}</span> · <span dir="ltr">{{ $student->nextLesson->starts_at->format('H:i') }} – {{ $student->nextLesson->ends_at->format('H:i') }}</span>
                            </p>
                        @endif
                        @if ($student->notes)
                            <p class="mt-2 line-clamp-2 text-xs text-muted">{{ $student->notes }}</p>
                        @endif
                    </div>

                    {{-- Menu d'actions --}}
                    <div x-data="{ menu: false }" class="relative" x-on:click.outside="menu = false" x-on:keydown.escape="menu = false">
                        <button type="button" x-on:click="menu = ! menu" :aria-expanded="menu" class="inline-flex size-9 items-center justify-center rounded-full text-muted transition hover:bg-sand-200 hover:text-ink">
                            <x-icon name="dots" class="size-5" /><span class="sr-only">{{ __('ui.actions') }}</span>
                        </button>
                        <div x-show="menu" x-transition.origin.top.right x-cloak class="absolute end-0 z-20 mt-1 w-60 rounded-2xl bg-white p-1.5 shadow-lift ring-1 ring-olive-100">
                            <button type="button" wire:click="edit({{ $student->id }})" x-on:click="menu = false" class="menu-item"><x-icon name="pencil" class="size-4" /> {{ __('ui.edit') }}</button>
                            @if ($student->is_individual)
                                <button type="button" wire:click="planLesson({{ $student->id }})" x-on:click="menu = false" class="menu-item"><x-icon name="calendar-days" class="size-4" /> {{ __('teacher.students.plan_lesson') }}</button>
                                <button type="button" x-on:click="menu = false; $dispatch('confirm', { title: {{ Js::from(__('teacher.students.stop_individual')) }}, message: {{ Js::from(__('teacher.students.stop_individual_message', ['name' => $student->full_name])) }}, onConfirm: () => $wire.stopIndividual({{ $student->id }}) })" class="menu-item text-red-600"><x-icon name="x-circle" class="size-4" /> {{ __('teacher.students.stop_individual') }}</button>
                            @else
                                <button type="button" wire:click="startIndividual({{ $student->id }})" x-on:click="menu = false" class="menu-item"><x-icon name="user" class="size-4" /> {{ __('teacher.students.start_individual') }}</button>
                            @endif
                            <a href="{{ $student->parentPhoneHref() }}" class="menu-item"><x-icon name="phone" class="size-4" /> {{ __('teacher.students.call_parent') }}</a>
                            <a href="{{ $student->parentWhatsappUrl() }}" target="_blank" rel="noopener" class="menu-item"><x-icon name="whatsapp" class="size-4" /> WhatsApp</a>
                        </div>
                    </div>
                </div>
            </li>
        @empty
            <li class="card flex flex-col items-center gap-2 p-10 text-center">
                <span class="flex size-14 items-center justify-center rounded-full bg-sand-200 text-subtle"><x-icon name="users" class="size-7" /></span>
                <p class="font-bold text-olive-700">{{ $search !== '' ? __('ui.no_results') : __('teacher.students.empty') }}</p>
                @if ($search === '')
                    <button type="button" wire:click="create" class="btn-primary mt-2"><x-icon name="plus" class="size-4" /> {{ __('teacher.students.add') }}</button>
                @endif
            </li>
        @endforelse
    </ul>

    @if ($students->hasPages())
        <div class="mt-4 flex justify-center">{{ $students->onEachSide(1)->links('tables.pagination') }}</div>
    @endif

    @include('livewire.teacher.partials.student-form', ['groupOptions' => $groupOptions])

    {{-- Programmer un cours individuel --}}
    <x-ui.modal wire:model="showLessonForm" :title="__('teacher.students.plan_lesson')"
                :description="$this->individualRateLabel">
        <form id="lesson-form" wire:submit="saveLesson" class="space-y-4">
            <x-ui.select wire:model.live="lesson.subject_id" :label="__('admin.lessons.subject')" :options="$subjectOptions" :placeholder="__('ui.choose')" required />
            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.input wire:model.live="lesson.date" type="date" :min="$today" :label="__('teacher.students.lesson_date')" required />
                <x-ui.input wire:model.live="lesson.starts_time" type="time" step="900" dir="ltr" :label="__('teacher.students.lesson_time')" required />
            </div>
            <x-ui.field :label="__('teacher.students.lesson_duration')" for="lesson.duration" required>
                <div class="segmented">
                    @foreach ($durationOptions as $minutes => $durationLabel)
                        <button type="button" wire:click="$set('lesson.duration', {{ $minutes }})" aria-selected="{{ (int) $lesson->duration === $minutes ? 'true' : 'false' }}" dir="ltr">{{ $durationLabel }}</button>
                    @endforeach
                </div>
            </x-ui.field>
            <x-ui.select wire:model.live="lesson.room_id" :label="__('admin.lessons.room')" :options="$roomOptions" :placeholder="__('ui.choose')" required />
        </form>

        <x-slot:footer>
            <button type="button" class="btn-ghost" x-on:click="open = false">{{ __('ui.cancel') }}</button>
            <button type="submit" form="lesson-form" class="btn-primary" wire:loading.attr="disabled" wire:target="saveLesson">
                <span wire:loading wire:target="saveLesson" class="spinner"></span> {{ __('teacher.students.plan') }}
            </button>
        </x-slot:footer>
    </x-ui.modal>
</div>
