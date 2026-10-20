{{-- Menu latéral de l'espace enseignant (voir App\View\Components\TeacherSidebar) --}}
<nav class="flex-1 space-y-6 overflow-y-auto px-3 py-4" aria-label="{{ __('teacher.space') }}">
    @foreach ($sections as $section)
        <div>
            <p x-show="! collapsed" class="mb-2 px-3 text-[11px] font-bold tracking-wider text-olive-200 uppercase">{{ $section['label'] }}</p>
            <ul class="space-y-1">
                @foreach ($section['items'] as $item)
                    <li>
                        <a href="{{ $item['url'] }}" wire:navigate title="{{ $item['label'] }}"
                           @class([
                               'press group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition',
                               'bg-terra-600 text-white shadow-soft' => $item['active'],
                               'text-olive-100 hover:bg-olive-700 hover:text-white' => ! $item['active'],
                           ])
                           @if ($item['active']) aria-current="page" @endif>
                            <x-icon :name="$item['icon']" class="size-5" />
                            <span x-show="! collapsed" class="truncate">{{ $item['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach
</nav>
