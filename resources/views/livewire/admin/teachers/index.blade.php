<div>
    <x-ui.page-header :title="__('admin.nav.teachers')" :description="__('admin.teachers.description')">
        <x-slot:actions>
            <button type="button" wire:click="create" class="btn-primary">
                <x-icon name="plus" class="size-4" /> {{ __('admin.teachers.add') }}
            </button>
        </x-slot:actions>
    </x-ui.page-header>

    @include('tables.data-table')

    <x-ui.modal wire:model="showForm" max-width="2xl"
                :title="$form->isEditing() ? __('admin.teachers.edit') : __('admin.teachers.add')"
                :description="$form->isEditing() ? null : __('admin.teachers.add_hint')">
        <form id="teacher-form" wire:submit="save" class="space-y-5">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.input wire:model.live.blur="form.name" :label="__('admin.teachers.name')" autocomplete="off" required />
                <x-ui.input wire:model.live.blur="form.phone" type="tel" dir="ltr" :label="__('admin.teachers.phone')" placeholder="0555 12 34 56" />
            </div>

            <fieldset class="space-y-4 rounded-2xl bg-sand-100 p-4">
                <legend class="flex items-center gap-2 px-1 text-sm font-bold text-olive-700">
                    <x-icon name="user-circle" class="size-5" /> {{ __('admin.teachers.account') }}
                </legend>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input wire:model.live.blur="form.email" type="email" dir="ltr" :label="__('admin.teachers.email')" autocomplete="off" required />
                    <x-ui.input wire:model.live.blur="form.password" type="password" dir="ltr" autocomplete="new-password"
                                :label="__('admin.teachers.password')"
                                :hint="$form->isEditing() ? __('admin.teachers.password_keep_hint') : __('admin.teachers.password_hint')"
                                :required="! $form->isEditing()" />
                </div>
                <x-ui.select wire:model="form.locale" :label="__('admin.teachers.locale')" :options="\App\Enums\Locale::options()" required />
            </fieldset>

            <x-ui.checkbox-group wire:model.live="form.subject_ids" :label="__('admin.teachers.subjects')" :options="$subjectOptions" :columns="3" required />

            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.input wire:model.live.blur="form.rate_per_lesson" type="number" min="0" step="100" :label="__('admin.teachers.rate')" :hint="__('admin.teachers.rate_hint')" required />
                <div class="flex items-end">
                    <x-ui.toggle wire:model="form.is_active" :label="__('ui.active')" :hint="__('admin.teachers.active_hint')" />
                </div>
            </div>
        </form>

        <x-slot:footer>
            <button type="button" class="btn-ghost" x-on:click="open = false">{{ __('ui.cancel') }}</button>
            <button type="submit" form="teacher-form" class="btn-primary" wire:loading.attr="disabled" wire:target="save">{{ __('ui.save') }}</button>
        </x-slot:footer>
    </x-ui.modal>
</div>
