<?php

namespace App\Models\Concerns;

use App\Support\Catalog;

/**
 * Vide le cache des données de référence quand le modèle change.
 */
trait FlushesCatalog
{
    public static function bootFlushesCatalog(): void
    {
        static::saved(fn () => Catalog::flush());
        static::deleted(fn () => Catalog::flush());
    }
}
