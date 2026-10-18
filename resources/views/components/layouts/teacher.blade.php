{{-- Espace enseignant : mobile d'abord, ressemble à une application (PWA plein écran) --}}
<x-layouts.base :title="$title ?? null" body-class="bg-sand-100">
    <div class="mx-auto flex min-h-dvh max-w-2xl flex-col">
        <header class="sticky top-0 z-30 flex items-center gap-2 bg-sand-100/90 px-4 pt-[max(0.75rem,env(safe-area-inset-top))] pb-3 backdrop-blur">
            <a href="{{ route('teacher.today') }}" wire:navigate class="flex items-center gap-2" dir="ltr">
                <x-brand.mark class="size-9" />
                <span class="leading-tight">
                    <span class="block font-['Baloo_2'] text-lg font-extrabold text-olive-700">Édu<span class="text-terra-600">cœur</span></span>
                </span>
            </a>
            <span class="ms-1 truncate text-sm font-semibold text-subtle">{{ auth()->user()->name }}</span>
            <div class="flex-1"></div>
            <x-locale-switcher />
            <livewire:notifications.bell />
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex size-10 items-center justify-center rounded-full text-muted hover:bg-sand-200" title="{{ __('auth.logout') }}">
                    <x-icon name="logout" class="flip-rtl" /><span class="sr-only">{{ __('auth.logout') }}</span>
                </button>
            </form>
        </header>

        <main class="flex-1 px-4 pt-2 pb-28">
            {{ $slot }}
        </main>

        {{-- Barre d'onglets du bas --}}
        <nav class="fixed inset-x-0 bottom-0 z-30 border-t border-olive-100 bg-white/95 pb-[env(safe-area-inset-bottom)] backdrop-blur" aria-label="{{ __('teacher.space') }}">
            <ul class="mx-auto grid max-w-2xl grid-cols-4">
                @foreach ([
                    ['teacher.today', 'today', 'sun'],
                    ['teacher.week', 'week', 'calendar'],
                    ['teacher.absence', 'absence', 'hand-raised'],
                    ['teacher.hours', 'hours', 'clock'],
                ] as [$route, $label, $icon])
                    @continue(! Route::has($route))
                    @php($active = request()->routeIs($route))
                    <li>
                        <a href="{{ route($route) }}" wire:navigate @if ($active) aria-current="page" @endif
                           @class(['flex flex-col items-center gap-1 py-2.5 text-[11px] font-bold transition', 'text-terra-700' => $active, 'text-subtle hover:text-olive-700' => ! $active])>
                            <span @class(['flex h-8 w-14 items-center justify-center rounded-full transition', 'bg-terra-50' => $active])>
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
