{{--
    Saisie bilingue FR / AR d'un même texte.
    <x-ui.translatable-input model="form.name" :label="__('...')" required />
    → lie form.name.fr et form.name.ar ; l'arabe se replie sur le français s'il est vide.
--}}
@props(['model', 'label', 'required' => false, 'multiline' => false, 'rows' => 3])

<fieldset class="space-y-2">
    <legend class="label">{{ $label }}@if ($required)<span class="text-terra-700" aria-hidden="true"> *</span>@endif</legend>
    <div class="grid gap-3 sm:grid-cols-2">
        @foreach (['fr' => __('ui.french'), 'ar' => __('ui.arabic')] as $lang => $langLabel)
            <x-ui.field :for="$model.'.'.$lang">
                <div class="relative">
                    <span class="pointer-events-none absolute end-3 top-2.5 rounded-md bg-sand-200 px-1.5 py-0.5 text-[10px] font-bold text-muted">{{ $lang === 'ar' ? 'ع' : 'FR' }}</span>
                    @if ($multiline)
                        <textarea wire:model.live.blur="{{ $model }}.{{ $lang }}" rows="{{ $rows }}" dir="{{ $lang === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ $lang }}"
                                  aria-label="{{ $label }} ({{ $langLabel }})"
                                  @class(['input pe-12', 'font-arabic' => $lang === 'ar', 'input-error' => $errors->has($model.'.'.$lang)])></textarea>
                    @else
                        <input type="text" wire:model.live.blur="{{ $model }}.{{ $lang }}" dir="{{ $lang === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ $lang }}"
                               aria-label="{{ $label }} ({{ $langLabel }})"
                               @class(['input pe-12', 'font-arabic' => $lang === 'ar', 'input-error' => $errors->has($model.'.'.$lang)])>
                    @endif
                </div>
            </x-ui.field>
        @endforeach
    </div>
    <p class="text-xs text-subtle">{{ __('ui.arabic_fallback_hint') }}</p>
</fieldset>
