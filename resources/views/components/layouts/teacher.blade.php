{{-- Espace enseignant : menu latéral (repliable sur ordinateur, tiroir sur mobile) --}}
<x-layouts.base :title="$title ?? null">
    <div x-data="{ collapsed: $persist(false).as('educoeur-teacher-sidebar'), drawer: false }"
         x-on:keydown.escape.window="drawer = false"
         class="min-h-dvh lg:flex">

        {{-- Fond du tiroir mobile --}}
        <div x-show="drawer" x-transition.opacity x-on:click="drawer = false" x-cloak class="fixed inset-0 z-40 bg-olive-900/40 lg:hidden"></div>

        {{-- Menu latéral --}}
        <aside :class="{ 'lg:w-20': collapsed, 'lg:w-64': ! collapsed, 'translate-x-0': drawer, 'ltr:-translate-x-full rtl:translate-x-full': ! drawer }"
               class="fixed inset-y-0 start-0 z-50 flex w-64 flex-col bg-olive-800 text-olive-100 transition-[width,transform] duration-200 lg:sticky lg:top-0 lg:h-dvh lg:translate-x-0 lg:ltr:translate-x-0 lg:rtl:translate-x-0">
            <div class="flex h-16 items-center justify-between gap-2 px-4">
                <a href="{{ route('teacher.today') }}" wire:navigate class="flex items-center gap-2.5 overflow-hidden" dir="ltr">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-sand-100">
                        <x-brand.mark class="size-8" />
                    </span>
                    <span x-show="! collapsed" class="font-['Baloo_2'] text-xl font-extrabold text-white">Édu<span class="text-terra-300">cœur</span></span>
                </a>
                <button type="button" x-on:click="drawer = false" class="rounded-full p-2 text-olive-200 hover:bg-olive-700 lg:hidden">
                    <x-icon name="x" /><span class="sr-only">{{ __('ui.close') }}</span>
                </button>
            </div>

            <x-teacher-sidebar />

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
            <header class="sticky top-0 z-30 flex h-16 items-center gap-2 border-b border-olive-100/70 bg-sand-100/85 px-4 pt-[env(safe-area-inset-top)] backdrop-blur sm:px-6">
                <button type="button" x-on:click="drawer = true" class="-ms-2 rounded-full p-2 text-muted hover:bg-sand-200 lg:hidden">
                    <x-icon name="bars" /><span class="sr-only">{{ __('ui.open_menu') }}</span>
                </button>

                <a href="{{ route('teacher.today') }}" wire:navigate class="lg:hidden"><x-brand.logo size="sm" /></a>

                <div class="flex-1"></div>

                <x-locale-switcher />
                <livewire:notifications.bell />

                {{-- Profil --}}
                <div x-data="{ open: false }" class="relative" x-on:click.outside="open = false" x-on:keydown.escape="open = false">
                    <button type="button" x-on:click="open = ! open" :aria-expanded="open" class="press flex items-center gap-2 rounded-full p-1 pe-2 hover:bg-sand-200">
                        <span class="flex size-9 items-center justify-center rounded-full bg-olive-700 text-sm font-bold text-white">{{ auth()->user()->initials() }}</span>
                        <x-icon name="chevron-down" class="hidden size-4 text-subtle sm:block" />
                        <span class="sr-only">{{ __('admin.profile') }}</span>
                    </button>
                    <div x-show="open" x-transition.origin.top x-cloak class="absolute end-0 mt-2 w-64 rounded-2xl bg-white p-2 shadow-lift ring-1 ring-olive-100">
                        <div class="px-3 py-2">
                            <p class="font-bold text-ink">{{ auth()->user()->name }}</p>
                            <p class="truncate text-xs text-subtle" dir="ltr">{{ auth()->user()->email }}</p>
                        </div>
                        <x-install-app-button />
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="menu-item">
                                <x-icon name="logout" class="flip-rtl size-5" /> {{ __('auth.logout') }}
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="mx-auto w-full max-w-5xl flex-1 px-4 py-6 sm:px-6 lg:py-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    <x-ui.confirm-dialog />
</x-layouts.base>
