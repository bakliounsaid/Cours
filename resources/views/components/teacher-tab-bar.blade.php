{{-- Barre d'onglets du bas, avec repère sous l'onglet actif (voir App\View\Components\TeacherTabBar) --}}
<nav class="fixed inset-x-0 bottom-0 z-30 border-t border-olive-100 bg-white/95 pb-[env(safe-area-inset-bottom)] backdrop-blur" aria-label="{{ __('teacher.space') }}">
    <ul class="relative mx-auto grid max-w-3xl" style="grid-template-columns: repeat({{ count($tabs) }}, minmax(0, 1fr))">
        @if ($activeIndex !== null)
            <span aria-hidden="true" class="tab-indicator" style="--tabs: {{ count($tabs) }}; --index: {{ $activeIndex }}"></span>
        @endif
        @foreach ($tabs as $tab)
            <li>
                <a href="{{ $tab['url'] }}" wire:navigate @if ($tab['active']) aria-current="page" @endif
                   @class(['press flex flex-col items-center gap-1 py-2.5 text-[11px] font-bold transition', 'text-terra-700' => $tab['active'], 'text-subtle hover:text-olive-700' => ! $tab['active']])>
                    <span @class(['flex h-8 w-14 items-center justify-center rounded-full transition', 'bg-terra-50 scale-105' => $tab['active']])>
                        <x-icon :name="$tab['icon']" class="size-6" />
                    </span>
                    {{ $tab['label'] }}
                </a>
            </li>
        @endforeach
    </ul>
</nav>
