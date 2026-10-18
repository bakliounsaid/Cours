<?php

namespace App\Livewire\Tables;

use App\Enums\Locale;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Tableau de données réutilisable : recherche, filtres, tri, pagination, export Excel.
 *
 * Une page de gestion étend cette classe et définit query(), columns(), et sa vue.
 * Dans la vue : @include('tables.data-table')
 */
abstract class DataTable extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'tri', except: '')]
    public string $sortField = '';

    #[Url(as: 'ordre', except: 'asc')]
    public string $sortDirection = 'asc';

    /** @var array<string, string> */
    #[Url(as: 'f', except: [])]
    public array $filters = [];

    public int $perPage = 15;

    /**
     * Requête de base (sans recherche, filtres ni tri).
     */
    abstract protected function query(): Builder;

    /**
     * @return list<Column>
     */
    abstract protected function columns(): array;

    /**
     * Vue de la page (qui inclut tables.data-table).
     */
    abstract protected function view(): string;

    /**
     * @return list<Filter>
     */
    protected function filters(): array
    {
        return [];
    }

    protected function applySearch(Builder $query, string $term): void {}

    /**
     * Tri par défaut quand aucune colonne n'est choisie.
     */
    protected function applyDefaultSort(Builder $query): void
    {
        $query->latest('id');
    }

    /**
     * Vue partielle des actions par ligne (reçoit $row), ou null.
     */
    protected function rowActionsView(): ?string
    {
        return null;
    }

    protected function exportFileName(): string
    {
        return 'export';
    }

    protected function isExportable(): bool
    {
        return true;
    }

    /**
     * Données supplémentaires pour la vue de la page.
     *
     * @return array<string, mixed>
     */
    protected function viewData(): array
    {
        return [];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilters(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->perPage = in_array($this->perPage, [10, 15, 25, 50], true) ? $this->perPage : 15;
        $this->resetPage();
    }

    public function sortBy(string $key): void
    {
        $column = collect($this->columns())->first(fn (Column $column) => $column->key === $key && $column->isSortable());

        if (! $column) {
            return;
        }

        $this->sortDirection = $this->sortField === $key && $this->sortDirection === 'asc' ? 'desc' : 'asc';
        $this->sortField = $key;
    }

    public function resetFilters(): void
    {
        $this->reset('filters', 'search');
        $this->resetPage();
    }

    public function export(): ?BinaryFileResponse
    {
        if (! $this->isExportable()) {
            return null;
        }

        return Excel::download(
            new DataTableExport($this->builder(), $this->columns(), Locale::current()->isRtl()),
            $this->exportFileName().'-'.now()->format('Y-m-d').'.xlsx',
        );
    }

    protected function builder(): Builder
    {
        $query = $this->query();

        $term = trim($this->search);

        if ($term !== '') {
            $this->applySearch($query, $term);
        }

        foreach ($this->filters() as $filter) {
            $filter->apply($query, (string) ($this->filters[$filter->key] ?? ''));
        }

        $column = collect($this->columns())->first(fn (Column $column) => $column->key === $this->sortField && $column->isSortable());

        if ($column) {
            $query->orderBy($column->sortColumn(), $this->sortDirection === 'desc' ? 'desc' : 'asc')->orderBy($query->getModel()->getQualifiedKeyName());
        } else {
            $this->applyDefaultSort($query);
        }

        return $query;
    }

    protected function rows(): LengthAwarePaginator
    {
        return $this->builder()->paginate($this->perPage);
    }

    public function render(): View
    {
        return view($this->view(), [
            'rows' => $this->rows(),
            'columns' => array_values(array_filter($this->columns(), fn (Column $column) => $column->isDisplayed())),
            'tableFilters' => $this->filters(),
            'rowActions' => $this->rowActionsView(),
            'exportable' => $this->isExportable(),
            ...$this->viewData(),
        ]);
    }
}
