<?php

namespace App\Enums\Concerns;

use Illuminate\Support\Str;

/**
 * Libellé traduit d'un enum : lang/{fr,ar}/enums.php → [nom_enum_en_snake_case][valeur].
 */
trait HasLabel
{
    public function label(): string
    {
        return __('enums.'.self::translationGroup().'.'.$this->value);
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    private static function translationGroup(): string
    {
        return Str::snake(class_basename(self::class));
    }
}
