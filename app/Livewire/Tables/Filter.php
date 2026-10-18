<?php

namespace App\Livewire\Tables;

use Closure;
use Illuminate\Database\Eloquent\Builder;

/**
 * Filtre « liste déroulante » d'un tableau de données.
 *
 * Filter::make('level', __('students.level'), $levels->pluck('name', 'id')->all(),
 *     fn (Builder $query, string $value) => $query->where('level_id', $value));
 */
final class Filter
{
    /**
     * @param  array<string|int, string>  $options
     */
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly array $options,
        private readonly Closure $apply,
    ) {}

    /**
     * @param  array<string|int, string>  $options
     */
    public static function make(string $key, string $label, array $options, Closure $apply): self
    {
        return new self($key, $label, $options, $apply);
    }

    public function apply(Builder $query, string $value): void
    {
        if ($value === '' || ! array_key_exists($value, $this->options)) {
            return;
        }

        ($this->apply)($query, $value);
    }
}
