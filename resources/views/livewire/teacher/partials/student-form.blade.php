{{--
    Modale de création / modification d'un élève (StudentForm).
    $levelOptions ; $groupOptions (null : le groupe est imposé par la page)
--}}
<x-ui.modal wire:model="showForm" max-width="xl"
            :title="$form->isEditing() ? __('teacher.students.edit') : __('teacher.students.add')"
            :description="$form->isEditing() ? null : __('teacher.students.add_hint')">
    <form id="student-form" wire:submit="save" class="space-y-4">
        <div class="grid gap-4 sm:grid-cols-2">
            <x-ui.input wire:model.live.blur="form.first_name" :label="__('teacher.students.first_name')" autocomplete="off" required />
            <x-ui.input wire:model.live.blur="form.last_name" :label="__('teacher.students.last_name')" autocomplete="off" required />
        </div>

        <x-ui.select wire:model.live="form.level_id" :label="__('teacher.students.level')" :options="$levelOptions" :placeholder="__('ui.choose')" required />

        <div class="grid gap-4 sm:grid-cols-2">
            <x-ui.input wire:model.live.blur="form.parent_phone" type="tel" dir="ltr" :label="__('teacher.students.parent_phone')" placeholder="0555 12 34 56" required />
            <x-ui.select wire:model="form.parent_locale" :label="__('teacher.students.parent_locale')" :options="$localeOptions" required />
        </div>

        @if (! $form->isEditing() && $groupOptions !== null)
            <fieldset class="space-y-3 rounded-2xl bg-sand-100 p-4">
                <legend class="flex items-center gap-2 px-1 text-sm font-bold text-olive-700">
                    <x-icon name="academic-cap" class="size-5" /> {{ __('teacher.students.follow_up') }}
                </legend>
                <x-ui.select wire:model.live="form.group_id" :label="__('teacher.students.join_group')" :options="$groupOptions" :placeholder="__('teacher.students.no_group')" />
                <x-ui.toggle wire:model.live="form.individual" :label="__('teacher.students.individual')"
                             :hint="$this->individualRateLabel" />
            </fieldset>
        @endif

        <x-ui.textarea wire:model.live.blur="form.notes" :label="__('teacher.students.notes')" rows="2" :placeholder="__('teacher.students.notes_placeholder')" />
    </form>

    <x-slot:footer>
        <button type="button" class="btn-ghost" x-on:click="open = false">{{ __('ui.cancel') }}</button>
        <button type="submit" form="student-form" class="btn-primary" wire:loading.attr="disabled" wire:target="save">
            <span wire:loading wire:target="save" class="spinner"></span> {{ __('ui.save') }}
        </button>
    </x-slot:footer>
</x-ui.modal>
