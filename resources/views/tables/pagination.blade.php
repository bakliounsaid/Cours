@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex items-center gap-1">
        <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" @disabled($paginator->onFirstPage())
                class="flex size-9 items-center justify-center rounded-full text-muted hover:bg-sand-200 disabled:opacity-30" aria-label="{{ __('pagination.previous') }}">
            <x-icon name="chevron-left" class="flip-rtl size-4" />
        </button>

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-1 text-subtle">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    <button type="button" wire:key="page-{{ $page }}" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                            @class([
                                'flex size-9 items-center justify-center rounded-full text-sm font-bold',
                                'bg-olive-700 text-white' => $page == $paginator->currentPage(),
                                'text-muted hover:bg-sand-200' => $page != $paginator->currentPage(),
                            ])
                            @if ($page == $paginator->currentPage()) aria-current="page" @endif>
                        {{ $page }}
                    </button>
                @endforeach
            @endif
        @endforeach

        <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" @disabled(! $paginator->hasMorePages())
                class="flex size-9 items-center justify-center rounded-full text-muted hover:bg-sand-200 disabled:opacity-30" aria-label="{{ __('pagination.next') }}">
            <x-icon name="chevron-right" class="flip-rtl size-4" />
        </button>
    </nav>
@endif
