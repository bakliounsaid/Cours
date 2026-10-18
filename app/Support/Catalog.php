<?php

namespace App\Support;

use App\Models\Level;
use App\Models\Room;
use App\Models\Subject;
use App\Models\TimeSlot;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Données de référence (matières, niveaux, créneaux, salles) mises en cache :
 * elles changent rarement et sont lues sur chaque page publique.
 * Le cache est vidé dès qu'un de ces modèles est modifié (trait FlushesCatalog).
 *
 * Laravel refuse de désérialiser des objets depuis le cache : on stocke les
 * attributs bruts et on reconstruit les modèles avec hydrate().
 */
class Catalog
{
    private const PREFIX = 'educoeur.catalog.';

    private const KEYS = ['subjects', 'levels', 'time_slots', 'rooms'];

    /**
     * @return Collection<int, Subject>
     */
    public static function activeSubjects(): Collection
    {
        return self::remember('subjects', Subject::class, fn () => Subject::query()->active()->ordered());
    }

    /**
     * @return Collection<int, Level>
     */
    public static function levels(): Collection
    {
        return self::remember('levels', Level::class, fn () => Level::query()->ordered());
    }

    /**
     * @return Collection<int, TimeSlot>
     */
    public static function activeTimeSlots(): Collection
    {
        return self::remember('time_slots', TimeSlot::class, fn () => TimeSlot::query()->active()->ordered());
    }

    /**
     * @return Collection<int, Room>
     */
    public static function rooms(): Collection
    {
        return self::remember('rooms', Room::class, fn () => Room::query()->ordered());
    }

    public static function flush(): void
    {
        foreach (self::KEYS as $key) {
            Cache::forget(self::PREFIX.$key);
        }
    }

    /**
     * @template TModel of Model
     *
     * @param  class-string<TModel>  $model
     * @param  callable(): Builder<TModel>  $query
     * @return Collection<int, TModel>
     */
    private static function remember(string $key, string $model, callable $query): Collection
    {
        $rows = Cache::rememberForever(
            self::PREFIX.$key,
            fn () => $query()->get()->map(fn (Model $row) => $row->getAttributes())->all(),
        );

        return $model::hydrate($rows);
    }
}
