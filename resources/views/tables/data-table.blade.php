{{--
    Tableau de données générique (voir App\Livewire\Tables\DataTable).
    Variables : $rows, $columns, $tableFilters, $rowActions, $exportable
--}}
<div class="card overflow-hidden">
    {{-- Barre d'outils --}}
    <div class="flex flex-col gap-3 border-b border-olive-100/70 p-4 lg:flex-row lg:items-center">
        <label class="relative block flex-1">
            <span class="sr-only">{{ __('ui.search') }}</span>
            <x-icon name="search" class="pointer-events-none absolute start-3.5 top-1/2 size-5 -translate-y-1/2 text-subtle" />
            <input type="search" wire:model.live.debounce.350ms="search" placeholder="{{ __('ui.search') }}" class="input ps-11">
        </label>

        <div class="flex flex-wrap items-center gap-2">
            @foreach ($tableFilters as $filter)
                <label wire:key="filter-{{ $filter->key }}">
                    <span class="sr-only">{{ $filter->label }}</span>
                    <select wire:model.live="filters.{{ $filter->key }}" class="input w-auto py-2 pe-9 text-sm">
                        <option value="">{{ $filter->label }} : {{ __('ui.all') }}</option>
                        @foreach ($filter->options as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            @endforeach

            @if ($search !== '' || array_filter($filters))
                <button type="button" wire:click="resetFilters" class="btn-ghost btn-sm">
                    <x-icon name="x" class="size-4" /> {{ __('ui.reset_filters') }}
                </button>
            @endif

            @if ($exportable)
                <button type="button" wire:click="export" class="btn-ghost btn-sm">
                    <x-icon name="download" class="size-4" /> {{ __('ui.export_excel') }}
                </button>
            @endif
        </div>
    </div>

    <div wire:loading.class="opacity-60" class="transition-opacity">
        @if ($rows->isEmpty())
            <div class="flex flex-col items-center gap-2 px-6 py-16 text-center">
                <span class="flex size-14 items-center justify-center rounded-full bg-sand-200 text-subtle">
                    <x-icon name="inbox" class="size-7" />
                </span>
                <p class="font-bold text-olive-700">{{ __('ui.no_results') }}</p>
                @if ($search !== '' || array_filter($filters))
                    <p class="text-sm text-subtle">{{ __('ui.no_results_hint') }}</p>
                @endif
            </div>
        @else
            {{-- Ordinateur : tableau --}}
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full text-sm">
                    <thead class="bg-sand-50 text-start text-xs font-bold tracking-wide text-muted uppercase">
                        <tr>
                            @foreach ($columns as $column)
                                <th scope="col" class="px-4 py-3 text-start whitespace-nowrap {{ $column->cssClasses() }}">
                                    @if ($column->isSortable())
                                        <button type="button" wire:click="sortBy('{{ $column->key }}')" class="inline-flex items-center gap-1 hover:text-ink">
                                            {{ $column->label }}
                                            @if ($sortField === $column->key)
                                                <x-icon :name="$sortDirection === 'asc' ? 'chevron-up' : 'chevron-down'" class="size-3.5 text-terra-700" />
                                            @else
                                                <x-icon name="arrows-up-down" class="size-3.5 opacity-40" />
                                            @endif
                                        </button>
                                    @else
                                        {{ $column->label }}
                                    @endif
                                </th>
                            @endforeach
                            @if ($rowActions)
                                <th scope="col" class="px-4 py-3 text-end"><span class="sr-only">{{ __('ui.actions') }}</span></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-olive-50">
                        @foreach ($rows as $row)
                            <tr wire:key="row-{{ $row->getKey() }}" class="transition hover:bg-sand-50">
                                @foreach ($columns as $column)
                                    <td class="px-4 py-3 text-olive-700 {{ $column->cssClasses() }}">{{ $column->render($row) }}</td>
                                @endforeach
                                @if ($rowActions)
                                    <td class="px-4 py-3 text-end whitespace-nowrap">@include($rowActions, ['row' => $row])</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile : cartes --}}
            <ul class="divide-y divide-olive-50 md:hidden">
                @foreach ($rows as $row)
                    <li wire:key="card-{{ $row->getKey() }}" class="space-y-1.5 p-4">
                        @foreach ($columns as $column)
                            @continue($column->isHiddenOnMobile())
                            <div @class(['flex items-start justify-between gap-3 text-sm', 'text-base font-bold text-ink' => $loop->first])>
                                @unless ($loop->first)
                                    <span class="text-subtle">{{ $column->label }}</span>
                                @endunless
                                <span class="text-end">{{ $column->render($row) }}</span>
                            </div>
                        @endforeach
                        @if ($rowActions)
                            <div class="flex justify-end gap-1 pt-1">@include($rowActions, ['row' => $row])</div>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    @if ($rows->hasPages() || $rows->total() > 10)
        <div class="flex flex-col items-center justify-between gap-3 border-t border-olive-100/70 p-4 sm:flex-row">
            <div class="flex items-center gap-2 text-sm text-muted">
                <span>{{ trans_choice('ui.results_count', $rows->total(), ['count' => $rows->total()]) }}</span>
                <span aria-hidden="true">·</span>
                <label class="flex items-center gap-2">
                    {{ __('ui.per_page') }}
                    <select wire:model.live="perPage" class="input w-auto py-1 pe-8 text-sm">
                        @foreach ([10, 15, 25, 50] as $size)
                            <option value="{{ $size }}">{{ $size }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            {{ $rows->onEachSide(1)->links('tables.pagination') }}
        </div>
    @endif
</div>
