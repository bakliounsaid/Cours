<?php

namespace App\Livewire\Tables;

use Closure;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

/**
 * Colonne d'un tableau de données.
 *
 * Column::make('last_name', __('students.name'))
 *     ->sortable()
 *     ->format(fn (Student $s) => $s->full_name)
 *     ->exportAs(fn (Student $s) => $s->full_name);
 */
final class Column
{
    private ?string $sortColumn = null;

    private ?Closure $formatter = null;

    private ?Closure $exporter = null;

    private bool $hiddenOnMobile = false;

    private bool $exportable = true;

    private bool $exportOnly = false;

    private string $classes = '';

    public function __construct(
        public readonly string $key,
        public readonly string $label,
    ) {}

    public static function make(string $key, string $label): self
    {
        return new self($key, $label);
    }

    /**
     * Rendre la colonne triable (par défaut sur la colonne SQL du même nom).
     */
    public function sortable(?string $column = null): self
    {
        $this->sortColumn = $column ?? $this->key;

        return $this;
    }

    /**
     * Affichage de la cellule. Peut renvoyer du texte (échappé) ou du HTML (Htmlable / View).
     */
    public function format(Closure $formatter): self
    {
        $this->formatter = $formatter;

        return $this;
    }

    /**
     * Valeur exportée dans Excel (par défaut : texte de la cellule).
     */
    public function exportAs(Closure $exporter): self
    {
        $this->exporter = $exporter;

        return $this;
    }

    public function notExportable(): self
    {
        $this->exportable = false;

        return $this;
    }

    /**
     * Colonne présente uniquement dans l'export Excel (pas à l'écran).
     */
    public function exportOnly(): self
    {
        $this->exportOnly = true;

        return $this;
    }

    public function hideOnMobile(): self
    {
        $this->hiddenOnMobile = true;

        return $this;
    }

    public function classes(string $classes): self
    {
        $this->classes = $classes;

        return $this;
    }

    public function isSortable(): bool
    {
        return $this->sortColumn !== null;
    }

    public function sortColumn(): ?string
    {
        return $this->sortColumn;
    }

    public function isHiddenOnMobile(): bool
    {
        return $this->hiddenOnMobile;
    }

    public function isDisplayed(): bool
    {
        return ! $this->exportOnly;
    }

    public function isExportable(): bool
    {
        return $this->exportable;
    }

    public function cssClasses(): string
    {
        return $this->classes;
    }

    public function render(Model $row): mixed
    {
        return $this->formatter ? ($this->formatter)($row) : data_get($row, $this->key);
    }

    public function exportValue(Model $row): string|int|float|null
    {
        if ($this->exporter) {
            return ($this->exporter)($row);
        }

        $value = $this->render($row);

        if ($value instanceof Htmlable) {
            $value = $value->toHtml();
        } elseif (is_object($value) && method_exists($value, 'render')) {
            $value = $value->render();
        } elseif ($value instanceof \BackedEnum) {
            $value = method_exists($value, 'label') ? $value->label() : $value->value;
        }

        return is_numeric($value) ? $value : trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $value))));
    }
}
