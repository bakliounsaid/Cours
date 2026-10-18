@php($other = \App\Enums\Locale::current()->other())

<a href="{{ route('locale.switch', $other) }}"
   hreflang="{{ $other->value }}"
   lang="{{ $other->value }}"
   title="{{ $other->nativeName() }}"
   {{ $attributes->merge(['class' => 'inline-flex size-10 items-center justify-center rounded-full bg-white font-bold text-olive-700 ring-1 ring-olive-100 transition hover:bg-terra-50 hover:text-terra-700']) }}>
    <span @class(['font-arabic text-lg' => $other->isRtl(), 'font-latin text-sm' => ! $other->isRtl()])>{{ $other->shortName() }}</span>
    <span class="sr-only">{{ __('app.switch_language', ['language' => $other->nativeName()]) }}</span>
</a>
