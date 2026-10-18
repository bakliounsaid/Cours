<?php

namespace App\Models\Concerns;

use App\Enums\Locale;

/**
 * Attributs enregistrés en deux versions {"fr": "...", "ar": "..."} (colonne jsonb).
 *
 * - $model->name               → texte dans la langue courante, repli sur le français si vide
 * - $model->translate('name', 'ar')
 * - $model->getTranslations('name') → ['fr' => ..., 'ar' => ...]
 * - $model->name = ['fr' => 'Mathématiques', 'ar' => 'الرياضيات']
 *
 * Le modèle déclare : protected array $translatable = ['name'];
 */
trait HasTranslations
{
    public function getAttributeValue($key): mixed
    {
        if (! $this->isTranslatableAttribute($key)) {
            return parent::getAttributeValue($key);
        }

        return $this->translate($key);
    }

    public function setAttribute($key, $value): mixed
    {
        if (! $this->isTranslatableAttribute($key)) {
            return parent::setAttribute($key, $value);
        }

        $translations = is_array($value)
            ? $value
            : [...$this->getTranslations($key), app()->getLocale() => $value];

        $this->attributes[$key] = json_encode(
            array_map(fn ($text) => $text === null ? null : trim((string) $text), array_intersect_key($translations, array_flip(Locale::values()))),
            JSON_UNESCAPED_UNICODE
        );

        return $this;
    }

    public function translate(string $key, ?string $locale = null): string
    {
        $translations = $this->getTranslations($key);
        $locale ??= app()->getLocale();

        if (filled($translations[$locale] ?? null)) {
            return $translations[$locale];
        }

        return (string) ($translations[config('app.fallback_locale')] ?? collect($translations)->filter()->first() ?? '');
    }

    /**
     * @return array<string, string|null>
     */
    public function getTranslations(string $key): array
    {
        $raw = $this->attributes[$key] ?? null;

        $decoded = is_array($raw) ? $raw : json_decode((string) $raw, true);

        return array_merge(array_fill_keys(Locale::values(), null), is_array($decoded) ? $decoded : []);
    }

    public function isTranslatableAttribute(string $key): bool
    {
        return in_array($key, $this->translatable ?? [], true);
    }

    /**
     * Les tableaux / JSON exposent les deux versions.
     *
     * @return array<string, mixed>
     */
    public function attributesToArray(): array
    {
        $attributes = parent::attributesToArray();

        foreach ($this->translatable ?? [] as $key) {
            if (array_key_exists($key, $attributes)) {
                $attributes[$key] = $this->getTranslations($key);
            }
        }

        return $attributes;
    }
}
