{{--
    Saisie d'un même texte dans toutes les langues (FR / AR / EN).
    <x-ui.translatable-input model="form.name" :label="__('...')" required />
    → lie form.name.fr, form.name.ar et form.name.en ; une version vide se replie sur le français.
--}}
@props(['model', 'label', 'required' => false, 'multiline' => false, 'rows' => 3])

<fieldset class="space-y-2">
    <legend class="label">{{ $label }}@if ($required)<span class="text-terra-700" aria-hidden="true"> *</span>@endif</legend>
    <div class="grid gap-3 sm:grid-cols-3">
        @foreach (\App\Enums\Locale::cases() as $lang)
            <x-ui.field :for="$model.'.'.$lang->value">
                <div class="relative">
                    <span class="pointer-events-none absolute end-3 top-2.5 rounded-md bg-sand-200 px-1.5 py-0.5 text-[10px] font-bold text-muted">{{ $lang->shortName() }}</span>
                    @if ($multiline)
                        <textarea wire:model.live.blur="{{ $model }}.{{ $lang->value }}" rows="{{ $rows }}" dir="{{ $lang->direction() }}" lang="{{ $lang->value }}"
                                  aria-label="{{ $label }} ({{ $lang->label() }})"
                                  @class(['input pe-12', 'font-arabic' => $lang->isRtl(), 'input-error' => $errors->has($model.'.'.$lang->value)])></textarea>
                    @else
                        <input type="text" wire:model.live.blur="{{ $model }}.{{ $lang->value }}" dir="{{ $lang->direction() }}" lang="{{ $lang->value }}"
                               aria-label="{{ $label }} ({{ $lang->label() }})"
                               @class(['input pe-12', 'font-arabic' => $lang->isRtl(), 'input-error' => $errors->has($model.'.'.$lang->value)])>
                    @endif
                </div>
            </x-ui.field>
        @endforeach
    </div>
    <p class="text-xs text-subtle">{{ __('ui.arabic_fallback_hint') }}</p>
</fieldset>
