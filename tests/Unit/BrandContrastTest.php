<?php

/*
 * Les couleurs de la charte (resources/css/app.css) doivent respecter WCAG AA :
 * 4,5:1 pour le texte normal, 3:1 pour le grand texte et les éléments non textuels.
 */

function brandColors(): array
{
    preg_match_all('/--color-([a-z0-9-]+):\s*(#[0-9a-f]{6});/i', file_get_contents(__DIR__.'/../../resources/css/app.css'), $matches);

    return array_combine($matches[1], $matches[2]) + ['white' => '#ffffff'];
}

function relativeLuminance(string $hex): float
{
    $channels = array_map(function (string $pair) {
        $value = hexdec($pair) / 255;

        return $value <= 0.03928 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
    }, str_split(ltrim($hex, '#'), 2));

    return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
}

function contrastRatio(string $a, string $b): float
{
    [$light, $dark] = [max(relativeLuminance($a), relativeLuminance($b)), min(relativeLuminance($a), relativeLuminance($b))];

    return ($light + 0.05) / ($dark + 0.05);
}

it('respecte WCAG AA pour :dataset', function (string $text, string $background, float $minimum) {
    $colors = brandColors();

    expect(contrastRatio($colors[$text], $colors[$background]))->toBeGreaterThanOrEqual($minimum);
})->with([
    'texte principal sur sable' => ['ink', 'sand-100', 4.5],
    'texte secondaire sur sable' => ['muted', 'sand-100', 4.5],
    'texte secondaire sur carte' => ['muted', 'white', 4.5],
    'aides sur sable' => ['subtle', 'sand-100', 4.5],
    'aides sur carte' => ['subtle', 'white', 4.5],
    'titres vert olive sur sable' => ['olive-700', 'sand-100', 4.5],
    'bouton vert olive' => ['white', 'olive-700', 4.5],
    'bouton terracotta' => ['white', 'terra-600', 4.5],
    'liens terracotta sur sable' => ['terra-700', 'sand-100', 4.5],
    'badge terracotta' => ['terra-700', 'terra-50', 4.5],
    'badge vert' => ['olive-700', 'olive-50', 4.5],
    'badge miel' => ['honey-700', 'honey-100', 4.5],
    'texte sur jaune miel' => ['ink', 'honey-400', 4.5],
    'texte sur vert sauge' => ['ink', 'sage-400', 4.5],
    'texte sur bandeau sombre' => ['olive-100', 'olive-800', 4.5],
    'libellés sur bandeau sombre' => ['olive-200', 'olive-800', 4.5],
    'surtitre sur bandeau sombre' => ['terra-200', 'olive-800', 4.5],
    'récit sur fond sauge' => ['ink', 'sage-100', 4.5],
    'texte secondaire sur fond sauge' => ['muted', 'sage-100', 4.5],
    'surtitre sur fond sauge' => ['terra-700', 'sage-100', 4.5],
    'grand « 2019 » sur fond sauge' => ['terra-600', 'sage-100', 3.0],
    'icônes terracotta sur sable' => ['terra-600', 'sand-100', 3.0],
    'bordures de champs' => ['olive-400', 'white', 3.0],
]);
