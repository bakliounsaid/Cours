<?php

namespace App\Settings;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Réglages de l'école, modifiables dans l'admin.
 * Les valeurs bilingues sont stockées sous la forme ['fr' => ..., 'ar' => ...].
 */
class Settings
{
    private const CACHE_KEY = 'educoeur.settings';

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => Setting::query()->pluck('value', 'key')->all());
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default ?? DefaultSettings::get($key);
    }

    public function set(string $key, mixed $value): void
    {
        $this->setMany([$key => $value]);
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Texte bilingue dans la langue courante, avec repli sur le français.
     */
    public function text(string $key, ?string $locale = null): string
    {
        $value = $this->get($key);

        if (! is_array($value)) {
            return (string) $value;
        }

        $locale ??= app()->getLocale();

        return (string) (filled($value[$locale] ?? null) ? $value[$locale] : ($value[config('app.fallback_locale')] ?? ''));
    }

    /** Règle de comptage : les absences consomment-elles une séance payée ? */
    public function countsAbsences(): bool
    {
        return (bool) $this->get('payments.count_absences');
    }

    /** Pré-rappel à la 3e séance du cycle. */
    public function preReminderEnabled(): bool
    {
        return (bool) $this->get('payments.pre_reminder');
    }

    public function lessonsPerCycle(): int
    {
        return (int) $this->get('payments.lessons_per_cycle');
    }
}
