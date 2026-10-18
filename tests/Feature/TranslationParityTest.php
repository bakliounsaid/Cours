<?php

use Illuminate\Support\Arr;

/**
 * Toute chaîne doit exister en français ET en arabe.
 */
it('a les mêmes clés de traduction en français et en arabe', function (string $file) {
    $fr = array_keys(Arr::dot(require lang_path("fr/{$file}")));
    $ar = array_keys(Arr::dot(require lang_path("ar/{$file}")));

    expect(array_values(array_diff($fr, $ar)))->toBe([], "Clés manquantes en arabe dans {$file}")
        ->and(array_values(array_diff($ar, $fr)))->toBe([], "Clés manquantes en français dans {$file}");
})->with(array_map('basename', glob(__DIR__.'/../../lang/fr/*.php')));
