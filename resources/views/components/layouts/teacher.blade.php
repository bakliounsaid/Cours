{{-- Espace enseignant : mobile d'abord, ressemble à une application (PWA plein écran) --}}
@php
    $tabs = collect([
        ['teacher.today', 'today', 'sun', 'teacher.today'],
        ['teacher.groups', 'groups', 'user-group', 'teacher.groups*'],
        ['teacher.students', 'students', 'users', 'teacher.students*'],
        ['teacher.week', 'week', 'calendar', 'teacher.week'],
        ['teacher.absence', 'absence', 'hand-raised', 'teacher.absence'],
        ['teacher.hours', 'hours', 'clock', 'teacher.hours'],
    ])->filter(fn (array $tab) => Route::has($tab[0]))->values();
    $activeIndex = $tabs->search(fn (array $tab) => request()->routeIs($tab[3]));
@endphp

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

        {{-- Barre d'onglets du bas, avec repère animé sous l'onglet actif --}}
        <nav class="fixed inset-x-0 bottom-0 z-30 border-t border-olive-100 bg-white/95 pb-[env(safe-area-inset-bottom)] backdrop-blur" aria-label="{{ __('teacher.space') }}">
            <ul class="relative mx-auto grid max-w-3xl" style="grid-template-columns: repeat({{ max($tabs->count(), 1) }}, minmax(0, 1fr))">
                @if ($activeIndex !== false)
                    <span aria-hidden="true" class="tab-indicator" style="--tabs: {{ $tabs->count() }}; --index: {{ $activeIndex }}"></span>
                @endif
                @foreach ($tabs as $index => [$route, $label, $icon])
                    @php($active = $index === $activeIndex)
                    <li>
                        <a href="{{ route($route) }}" wire:navigate @if ($active) aria-current="page" @endif
                           @class(['press flex flex-col items-center gap-1 py-2.5 text-[11px] font-bold transition', 'text-terra-700' => $active, 'text-subtle hover:text-olive-700' => ! $active])>
                            <span @class(['flex h-8 w-14 items-center justify-center rounded-full transition', 'bg-terra-50 scale-105' => $active])>
                                <x-icon :name="$icon" class="size-6" />
                            </span>
                            {{ __('teacher.nav.'.$label) }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>
    </div>

    <x-ui.confirm-dialog />
</x-layouts.base>
