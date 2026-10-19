<div>
    <div class="animate-fade-up mb-5 flex items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold">{{ __('teacher.nav.groups') }}</h1>
            <p class="text-sm text-muted">{{ __('teacher.groups.description') }}</p>
        </div>
        <button type="button" wire:click="create" class="btn-primary shrink-0">
            <x-icon name="plus" class="size-4" /> <span class="hidden sm:inline">{{ __('teacher.groups.add') }}</span><span class="sm:hidden">{{ __('ui.add') }}</span>
        </button>
    </div>

    {{-- Recherche et onglets --}}
    <div class="animate-fade-up mb-4 space-y-3 [animation-delay:60ms]">
        <label class="relative block">
            <span class="sr-only">{{ __('ui.search') }}</span>
            <x-icon name="search" class="pointer-events-none absolute start-3.5 top-1/2 size-5 -translate-y-1/2 text-subtle" />
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="{{ __('teacher.groups.search') }}" class="input ps-11">
        </label>
        <div class="segmented" role="tablist">
            <button type="button" role="tab" wire:click="$set('status', 'active')" aria-selected="{{ $status !== 'archived' ? 'true' : 'false' }}">{{ __('teacher.groups.active') }}</button>
            <button type="button" role="tab" wire:click="$set('status', 'archived')" aria-selected="{{ $status === 'archived' ? 'true' : 'false' }}">
                {{ __('teacher.groups.archived_tab') }} <span class="rounded-full bg-sand-200 px-1.5 text-[11px] text-muted">{{ $archivedCount }}</span>
            </button>
        </div>
    </div>

    <div wire:loading.class="opacity-60" wire:target="search,status" class="grid gap-3 transition-opacity sm:grid-cols-2">
        @forelse ($groups as $group)
            @php
                $capacity = min($group->room->capacity, (int) config('educoeur.groups.max_students'));
                $fill = $capacity > 0 ? min(100, round($group->students_count / $capacity * 100)) : 0;
            @endphp
            <article wire:key="group-{{ $group->id }}" style="--subject: {{ $group->subject->color }}; animation-delay: {{ min($loop->index * 50, 400) }}ms"
                     class="animate-fade-up card card-hover relative flex flex-col overflow-hidden">
                <span class="absolute inset-x-0 top-0 h-1.5 bg-(--subject)"></span>
                <a href="{{ route('teacher.groups.show', $group) }}" wire:navigate class="block flex-1 p-4 pt-5">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <h2 class="truncate text-lg font-bold">{{ $group->name }}</h2>
                            <p class="truncate text-sm text-muted">{{ $group->subject->name }} · {{ $group->level->name }}</p>
                        </div>
                        <span class="badge shrink-0 text-white" style="background: {{ $group->room->color }}">{{ $group->room->name }}</span>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-1.5">
                        @forelse ($group->timeSlots as $slot)
                            <span class="badge bg-sand-200 text-olive-700 capitalize">{{ $slot->label() }}</span>
                        @empty
                            <span class="text-xs text-subtle">{{ __('teacher.groups.no_slots') }}</span>
                        @endforelse
                    </div>

                    <div class="mt-4">
                        <div class="mb-1 flex justify-between text-xs font-semibold">
                            <span class="text-muted">{{ trans_choice('teacher.students.count', $group->students_count, ['count' => $group->students_count]) }}</span>
                            <span @class(['text-terra-700' => $fill >= 100, 'text-subtle' => $fill < 100])>{{ $fill >= 100 ? __('teacher.groups.full_short') : __('teacher.groups.places_left', ['count' => $capacity - $group->students_count]) }}</span>
                        </div>
                        <div class="progress"><span style="width: {{ $fill }}%" @class(['bg-terra-500' => $fill >= 100, 'bg-sage-500' => $fill < 100])></span></div>
                    </div>
                </a>

                <div class="flex items-center justify-between border-t border-olive-100/70 px-3 py-2">
                    <p class="text-xs text-subtle">{{ __('teacher.rates.group_value', ['amount' => \App\Support\Format::money($this->teacher->rate_group)]) }}</p>
                    <div class="flex gap-1">
                        <x-ui.icon-button icon="pencil" :label="__('ui.edit')" wire:click="edit({{ $group->id }})" />
                        @if ($group->is_active)
                            <x-ui.icon-button icon="inbox" :label="__('teacher.groups.archive')"
                                x-on:click="$dispatch('confirm', { title: {{ Js::from(__('teacher.groups.archive_title')) }}, message: {{ Js::from(__('teacher.groups.archive_message', ['name' => $group->name])) }}, onConfirm: () => $wire.archive({{ $group->id }}) })" />
                        @else
                            <x-ui.icon-button icon="refresh" :label="__('teacher.groups.restore')" wire:click="restore({{ $group->id }})" />
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="card col-span-full flex flex-col items-center gap-2 p-10 text-center">
                <span class="flex size-14 items-center justify-center rounded-full bg-sand-200 text-subtle"><x-icon name="user-group" class="size-7" /></span>
                <p class="font-bold text-olive-700">{{ $status === 'archived' ? __('teacher.groups.no_archived') : __('teacher.groups.empty') }}</p>
                @if ($status !== 'archived')
                    <button type="button" wire:click="create" class="btn-primary mt-2"><x-icon name="plus" class="size-4" /> {{ __('teacher.groups.add') }}</button>
                @endif
            </div>
        @endforelse
    </div>

    {{-- Formulaire du groupe --}}
    <x-ui.modal wire:model="showForm" max-width="2xl" :title="$form->isEditing() ? __('teacher.groups.edit') : __('teacher.groups.add')">
        <form id="group-form" wire:submit="save" class="space-y-5">
            <x-ui.input wire:model.live.blur="form.name" :label="__('teacher.groups.name')" :placeholder="__('teacher.groups.name_placeholder')" required />

            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.select wire:model.live="form.subject_id" :label="__('admin.lessons.subject')" :options="$subjectOptions" :placeholder="__('ui.choose')" required />
                <x-ui.select wire:model.live="form.level_id" :label="__('teacher.students.level')" :options="$levelOptions" :placeholder="__('ui.choose')" required />
                <x-ui.select wire:model.live="form.room_id" :label="__('admin.lessons.room')" :options="$roomOptions" :placeholder="__('ui.choose')" required />
                <x-ui.input wire:model.live="form.starts_on" type="date" :label="__('teacher.groups.starts_on')" required />
            </div>

            {{-- Créneaux : puces à cocher, groupées par jour --}}
            <x-ui.field :label="__('teacher.groups.slots')" for="form.time_slot_ids" :hint="__('teacher.groups.slots_hint')" required>
                <div class="space-y-2.5">
                    @foreach ($slotsByDay as $day => $dayGroup)
                        <div wire:key="day-{{ $day }}" class="flex flex-wrap items-center gap-2">
                            <span class="w-24 shrink-0 text-sm font-bold text-olive-700 capitalize">{{ $dayGroup['day'] }}</span>
                            @foreach ($dayGroup['slots'] as $slotId => $slotLabel)
                                <label wire:key="slot-{{ $slotId }}" class="chip" dir="ltr">
                                    <input type="checkbox" value="{{ $slotId }}" wire:model.live="form.time_slot_ids" class="sr-only">
                                    {{ $slotLabel }}
                                </label>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </x-ui.field>

            <x-ui.toggle wire:model.live="form.is_active" :label="__('ui.active')" :hint="__('teacher.groups.active_hint')" />
        </form>

        <x-slot:footer>
            <button type="button" class="btn-ghost" x-on:click="open = false">{{ __('ui.cancel') }}</button>
            <button type="submit" form="group-form" class="btn-primary" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading wire:target="save" class="spinner"></span> {{ __('ui.save') }}
            </button>
        </x-slot:footer>
    </x-ui.modal>
</div>
