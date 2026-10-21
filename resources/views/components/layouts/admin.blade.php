<x-layouts.base :title="$title ?? null">
    <div x-data="{ collapsed: $persist(false).as('educoeur-sidebar'), drawer: false }"
         x-on:keydown.escape.window="drawer = false"
         class="min-h-dvh lg:flex">

        {{-- Fond du tiroir mobile --}}
        <div x-show="drawer" x-transition.opacity x-on:click="drawer = false" x-cloak class="fixed inset-0 z-40 bg-olive-900/40 lg:hidden"></div>

        {{-- Barre latérale --}}
        <aside :class="{ 'lg:w-20': collapsed, 'lg:w-72': ! collapsed, 'translate-x-0': drawer, 'ltr:-translate-x-full rtl:translate-x-full': ! drawer }"
               class="fixed inset-y-0 start-0 z-50 flex w-72 flex-col bg-olive-800 text-olive-100 transition-[width,transform] duration-200 lg:sticky lg:top-0 lg:h-dvh lg:translate-x-0 lg:ltr:translate-x-0 lg:rtl:translate-x-0">
            <div class="flex h-16 items-center justify-between gap-2 px-4">
                <a href="{{ route('admin.dashboard') }}" wire:navigate class="flex items-center gap-2.5 overflow-hidden" dir="ltr">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-sand-100">
                        <x-brand.mark class="size-8" />
                    </span>
                    <span x-show="! collapsed" class="font-['Baloo_2'] text-xl font-extrabold text-white">Édu<span class="text-terra-300">cœur</span></span>
                </a>
                <button type="button" x-on:click="drawer = false" class="rounded-full p-2 text-olive-200 hover:bg-olive-700 lg:hidden">
                    <x-icon name="x" /><span class="sr-only">{{ __('ui.close') }}</span>
                </button>
            </div>

            <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-4" aria-label="{{ __('admin.space') }}">
                @foreach (\App\Support\AdminNavigation::sections() as $section)
                    <div>
                        <p x-show="! collapsed" class="mb-2 px-3 text-[11px] font-bold tracking-wider text-olive-200 uppercase">{{ $section['label'] }}</p>
                        <ul class="space-y-1">
                            @foreach ($section['items'] as $item)
                                @php($active = request()->routeIs($item['active']))
                                <li>
                                    <a href="{{ route($item['route']) }}" wire:navigate
                                       title="{{ $item['label'] }}"
                                       @class([
                                           'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition',
                                           'bg-terra-600 text-white shadow-soft' => $active,
                                           'text-olive-100 hover:bg-olive-700 hover:text-white' => ! $active,
                                       ])
                                       @if ($active) aria-current="page" @endif>
                                        <x-icon :name="$item['icon']" class="size-5" />
                                        <span x-show="! collapsed" class="truncate">{{ $item['label'] }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </nav>

            <div class="hidden border-t border-olive-700 p-3 lg:block">
                <button type="button" x-on:click="collapsed = ! collapsed"
                        class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-olive-200 hover:bg-olive-700 hover:text-white">
                    <x-icon name="chevron-left" class="size-5 transition" x-bind:class="collapsed ? 'rotate-180 rtl:rotate-0' : 'rtl:rotate-180'" />
                    <span x-show="! collapsed">{{ __('ui.collapse_menu') }}</span>
                </button>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            {{-- En-tête --}}
            <header class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-olive-100/70 bg-sand-100/85 px-4 backdrop-blur sm:px-6">
                <button type="button" x-on:click="drawer = true" class="-ms-2 rounded-full p-2 text-muted hover:bg-sand-200 lg:hidden">
                    <x-icon name="bars" /><span class="sr-only">{{ __('ui.open_menu') }}</span>
                </button>

                <a href="{{ route('admin.dashboard') }}" wire:navigate class="lg:hidden"><x-brand.logo size="sm" /></a>

                <div class="flex-1"></div>

                <x-locale-switcher />
                <livewire:notifications.bell />

                {{-- Profil --}}
                <div x-data="{ open: false }" class="relative" x-on:click.outside="open = false">
                    <button type="button" x-on:click="open = ! open" class="flex items-center gap-2 rounded-full p-1 pe-2 hover:bg-sand-200" :aria-expanded="open">
                        <span class="flex size-9 items-center justify-center rounded-full bg-olive-700 text-sm font-bold text-white">{{ auth()->user()->initials() }}</span>
                        <x-icon name="chevron-down" class="hidden size-4 text-subtle sm:block" />
                    </button>
                    <div x-show="open" x-transition.origin.top x-cloak class="absolute end-0 mt-2 w-64 rounded-2xl bg-white p-2 shadow-lift ring-1 ring-olive-100">
                        <div class="px-3 py-2">
                            <p class="font-bold text-ink">{{ auth()->user()->name }}</p>
                            <p class="truncate text-xs text-subtle" dir="ltr">{{ auth()->user()->email }}</p>
                            <x-ui.badge class="mt-1.5" color="bg-terra-50 text-terra-700">{{ auth()->user()->role->label() }}</x-ui.badge>
                        </div>
                        <x-install-app-button />
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-muted hover:bg-sand-100">
                                <x-icon name="logout" class="flip-rtl size-5" /> {{ __('auth.logout') }}
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-6 sm:px-6 lg:py-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    <x-ui.confirm-dialog />
</x-layouts.base>
