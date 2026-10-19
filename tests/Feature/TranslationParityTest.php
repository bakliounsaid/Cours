<?php

use Illuminate\Support\Arr;

/**
 * Toute chaîne doit exister en français, en arabe ET en anglais.
 */
it('a les mêmes clés de traduction dans toutes les langues', function (string $file, string $locale) {
    $fr = array_keys(Arr::dot(require lang_path("fr/{$file}")));
    $other = array_keys(Arr::dot(require lang_path("{$locale}/{$file}")));

    expect(array_values(array_diff($fr, $other)))->toBe([], "Clés manquantes en « {$locale} » dans {$file}")
        ->and(array_values(array_diff($other, $fr)))->toBe([], "Clés en trop en « {$locale} » dans {$file}");
})->with(array_map('basename', glob(__DIR__.'/../../lang/fr/*.php')))->with(['ar', 'en']);
