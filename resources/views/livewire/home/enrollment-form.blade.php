<div>
    @if ($submitted)
        <div class="flex flex-col items-center py-10 text-center" role="status">
            <span class="flex size-20 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                <x-icon name="check-circle" class="size-11" />
            </span>
            <h3 class="mt-5 text-2xl font-extrabold">{{ __('home.form.success_title') }}</h3>
            <p class="mt-2 max-w-sm text-muted">{{ __('home.form.success_text') }}</p>
            <button type="button" wire:click="again" class="btn-ghost mt-6">{{ __('home.form.again') }}</button>
        </div>
    @else
        <form wire:submit="submit" class="space-y-5" novalidate>
            <x-ui.input wire:model.live.blur="student_name" :label="__('home.form.student_name')" :placeholder="__('home.form.student_name_placeholder')" autocomplete="name" required />

            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.field :label="__('home.form.level')" for="level_id" required>
                    <select id="level_id" wire:model.live="level_id" @class(['input pe-9', 'input-error' => $errors->has('level_id')]) required>
                        <option value="">{{ __('home.form.level_placeholder') }}</option>
                        @foreach ($this->levelOptions as $cycleLabel => $levels)
                            <optgroup label="{{ $cycleLabel }}">
                                @foreach ($levels as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </x-ui.field>

                <x-ui.input wire:model.live.blur="parent_phone" type="tel" inputmode="tel" dir="ltr" :label="__('home.form.parent_phone')" :placeholder="__('home.form.parent_phone_placeholder')" autocomplete="tel" required />
            </div>

            <x-ui.field :label="__('home.form.subjects')" for="subject_ids" required>
                <div class="flex flex-wrap gap-2">
                    @foreach ($this->subjectOptions as $id => $name)
                        <label wire:key="subject-{{ $id }}" class="cursor-pointer">
                            <input type="checkbox" value="{{ $id }}" wire:model.live="subject_ids" class="peer sr-only">
                            <span class="inline-flex items-center gap-1.5 rounded-full px-4 py-2 text-sm font-bold text-muted ring-1 ring-olive-200 transition peer-checked:bg-terra-600 peer-checked:text-white peer-checked:ring-terra-500 peer-focus-visible:ring-2 peer-focus-visible:ring-terra-300 hover:bg-terra-50">
                                {{ $name }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </x-ui.field>

            <x-ui.textarea wire:model.live.blur="message" :label="__('home.form.message')" :placeholder="__('home.form.message_placeholder')" rows="3" />

            {{-- Champ piège anti-robots, invisible pour les humains --}}
            <div class="absolute -start-[9999px]" aria-hidden="true">
                <label>Website <input type="text" wire:model="website" tabindex="-1" autocomplete="off"></label>
            </div>

            <button type="submit" class="btn-primary w-full py-3.5 text-base" wire:loading.attr="disabled" wire:target="submit">
                <span wire:loading.remove wire:target="submit" class="inline-flex items-center gap-2"><x-icon name="paper-airplane" class="size-5 rtl:-scale-x-100" /> {{ __('home.form.submit') }}</span>
                <span wire:loading wire:target="submit">{{ __('ui.loading') }}</span>
            </button>
            <p class="flex items-center justify-center gap-1.5 text-xs text-subtle"><x-icon name="shield-check" class="size-4" /> {{ __('home.form.privacy') }}</p>
        </form>
    @endif
</div>
