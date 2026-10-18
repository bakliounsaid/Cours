{{--
    Champ de formulaire : libellé + contenu + message d'erreur.
    <x-ui.field :label="__('...')" for="form.first_name" required> <input …> </x-ui.field>
--}}
@props(['label' => null, 'for' => null, 'hint' => null, 'required' => false])

<div {{ $attributes->merge(['class' => 'space-y-1.5']) }}>
    @if ($label)
        <label @if ($for) for="{{ $for }}" @endif class="label">
            {{ $label }}@if ($required)<span class="text-terra-700" aria-hidden="true"> *</span>@endif
        </label>
    @endif

    {{ $slot }}

    @if ($hint)
        <p class="text-xs text-subtle">{{ $hint }}</p>
    @endif

    @if ($for)
        @error($for)
            <p class="flex items-center gap-1 text-xs font-semibold text-red-600" role="alert">
                <x-icon name="warning" class="size-3.5" /> {{ $message }}
            </p>
        @enderror
    @endif
</div>
