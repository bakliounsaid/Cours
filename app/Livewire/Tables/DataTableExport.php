<?php

namespace App\Livewire\Tables;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Export Excel générique d'un tableau de données (mêmes filtres, même tri).
 */
class DataTableExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    /** @var list<Column> */
    private array $columns;

    /**
     * @param  list<Column>  $columns
     */
    public function __construct(
        private readonly Builder $query,
        array $columns,
        private readonly bool $rightToLeft = false,
    ) {
        $this->columns = array_values(array_filter($columns, fn (Column $column) => $column->isExportable()));
    }

    public function query(): Builder
    {
        return $this->query;
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return array_map(fn (Column $column) => $column->label, $this->columns);
    }

    /**
     * @param  Model  $row
     * @return list<string|int|float|null>
     */
    public function map($row): array
    {
        return array_map(fn (Column $column) => $column->exportValue($row), $this->columns);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        $sheet->setRightToLeft($this->rightToLeft);

        return [1 => ['font' => ['bold' => true]]];
    }
}
