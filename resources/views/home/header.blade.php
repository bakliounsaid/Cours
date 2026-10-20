{{-- En-tête de la page d'accueil : $navLinks fourni par App\Livewire\Home\HomePage --}}
<header x-data="{ scrolled: false, menu: false }"
        x-init="scrolled = window.scrollY > 10"
        x-on:scroll.window="scrolled = window.scrollY > 10"
        x-on:keydown.escape.window="menu = false"
        :class="scrolled || menu ? 'bg-white/90 shadow-soft backdrop-blur' : 'bg-transparent'"
        class="fixed inset-x-0 top-0 z-50 transition-all duration-300">
    <div class="mx-auto flex h-18 max-w-6xl items-center gap-4 px-4 py-3 sm:px-6">
        <a href="{{ route('home.localized', $locale->value) }}" class="rounded-xl focus-visible:outline-2 focus-visible:outline-terra-500" aria-label="{{ __('app.name') }}">
            <x-brand.logo />
        </a>

        <nav class="ms-6 hidden flex-1 items-center gap-1 lg:flex" aria-label="{{ __('home.nav.menu') }}">
            @foreach ($navLinks as $anchor => $label)
                <a href="#{{ $anchor }}" class="rounded-full px-3.5 py-2 text-sm font-bold text-muted transition hover:bg-terra-50 hover:text-terra-700">{{ $label }}</a>
            @endforeach
        </nav>

        <div class="ms-auto flex items-center gap-2">
            <x-locale-switcher />
            <a href="#contact" class="btn-primary hidden sm:inline-flex">{{ __('home.enroll_cta') }}</a>
            <button type="button" x-on:click="menu = ! menu" class="flex size-10 items-center justify-center rounded-full bg-white text-olive-700 ring-1 ring-olive-100 lg:hidden" :aria-expanded="menu">
                <x-icon name="bars" x-show="! menu" />
                <x-icon name="x" x-show="menu" x-cloak />
                <span class="sr-only">{{ __('home.nav.menu') }}</span>
            </button>
        </div>
    </div>

    {{-- Menu mobile --}}
    <nav x-show="menu" x-collapse x-cloak class="border-t border-olive-50 bg-white lg:hidden" aria-label="{{ __('home.nav.menu') }}">
        <ul class="mx-auto max-w-6xl space-y-1 px-4 py-4">
            @foreach ($navLinks as $anchor => $label)
                <li><a href="#{{ $anchor }}" x-on:click="menu = false" class="block rounded-xl px-4 py-3 font-bold text-olive-700 hover:bg-sand-100">{{ $label }}</a></li>
            @endforeach
            <li class="pt-2"><a href="#contact" x-on:click="menu = false" class="btn-primary w-full py-3">{{ __('home.enroll_cta') }}</a></li>
        </ul>
    </nav>
</header>
