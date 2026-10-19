{{-- Choix de la langue, gardée en session (voir App\View\Components\LocaleSwitcher) --}}
<div x-data="{ open: false }" x-on:click.outside="open = false" x-on:keydown.escape="open = false" {{ $attributes->merge(['class' => 'relative']) }}>
    <button type="button" x-on:click="open = ! open" :aria-expanded="open" aria-haspopup="menu"
            title="{{ __('app.choose_language') }}"
            class="press inline-flex h-10 items-center gap-1.5 rounded-full bg-white px-3 font-bold text-olive-700 ring-1 ring-olive-100 transition hover:bg-terra-50 hover:text-terra-700">
        <x-flag :locale="$current" />
        <span @class(['font-arabic text-base' => $current->isRtl(), 'font-latin text-sm' => ! $current->isRtl()])>{{ $current->shortName() }}</span>
        <x-icon name="chevron-down" class="size-3.5 transition" x-bind:class="open && 'rotate-180'" />
        <span class="sr-only">{{ __('app.choose_language') }}</span>
    </button>

    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
         x-transition:leave="transition ease-in duration-100" x-transition:leave-end="opacity-0 scale-95"
         role="menu"
         class="absolute end-0 z-50 mt-2 w-48 origin-top rounded-2xl bg-white p-1.5 shadow-lift ring-1 ring-olive-100">
        @foreach ($locales as $locale)
            <a href="{{ route('locale.switch', $locale) }}" role="menuitemradio" aria-checked="{{ $locale === $current ? 'true' : 'false' }}"
               hreflang="{{ $locale->value }}" lang="{{ $locale->value }}" dir="{{ $locale->direction() }}"
               @class([
                   'flex items-center justify-between gap-2 rounded-xl px-3 py-2 text-sm font-bold transition',
                   'bg-terra-50 text-terra-700' => $locale === $current,
                   'text-olive-700 hover:bg-sand-100' => $locale !== $current,
                   'font-arabic' => $locale->isRtl(),
               ])>
                <span class="flex items-center gap-2.5"><x-flag :locale="$locale" class="h-[18px] w-[27px]" /> {{ $locale->nativeName() }}</span>
                @if ($locale === $current)
                    <x-icon name="check" class="size-4" />
                @else
                    <span class="font-latin text-[11px] text-subtle">{{ $locale->shortName() }}</span>
                @endif
            </a>
        @endforeach
    </div>
</div>
