{{-- Espace enseignant : mobile d'abord, ressemble à une application (PWA plein écran) --}}
<x-layouts.base :title="$title ?? null" body-class="bg-sand-100">
    <div class="mx-auto flex min-h-dvh max-w-3xl flex-col">
        <header x-data="{ scrolled: false }" x-init="scrolled = window.scrollY > 4" x-on:scroll.window="scrolled = window.scrollY > 4"
                :class="scrolled && 'shadow-soft'"
                class="sticky top-0 z-30 flex items-center gap-2 bg-sand-100/90 px-4 pt-[max(0.75rem,env(safe-area-inset-top))] pb-3 backdrop-blur transition-shadow">
            <a href="{{ route('teacher.today') }}" wire:navigate class="press flex items-center gap-2" dir="ltr">
                <x-brand.mark class="size-9" />
                <span class="font-['Baloo_2'] text-lg font-extrabold text-olive-700">Édu<span class="text-terra-600">cœur</span></span>
            </a>
            <div class="flex-1"></div>
            <x-locale-switcher />
            <livewire:notifications.bell />

            {{-- Profil --}}
            <div x-data="{ open: false }" class="relative" x-on:click.outside="open = false" x-on:keydown.escape="open = false">
                <button type="button" x-on:click="open = ! open" :aria-expanded="open" class="press flex size-10 items-center justify-center rounded-full bg-olive-700 text-sm font-bold text-white ring-2 ring-white">
                    {{ auth()->user()->initials() }}<span class="sr-only">{{ __('admin.profile') }}</span>
                </button>
                <div x-show="open" x-transition.origin.top x-cloak class="absolute end-0 mt-2 w-64 rounded-2xl bg-white p-2 shadow-lift ring-1 ring-olive-100">
                    <div class="px-3 py-2">
                        <p class="font-bold text-ink">{{ auth()->user()->name }}</p>
                        <p class="truncate text-xs text-subtle" dir="ltr">{{ auth()->user()->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="menu-item">
                            <x-icon name="logout" class="flip-rtl size-5" /> {{ __('auth.logout') }}
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="flex-1 px-4 pt-2 pb-28">
            {{ $slot }}
        </main>

        <x-teacher-tab-bar />
    </div>

    <x-ui.confirm-dialog />
</x-layouts.base>
