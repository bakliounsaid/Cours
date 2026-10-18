<?php

namespace App\Notifications\Concerns;

use App\Support\Format;
use Illuminate\Notifications\DatabaseNotification;

/**
 * Les notifications « application » stockent une clé de traduction et ses paramètres,
 * pas une phrase : le texte est traduit à l'affichage, dans la langue du lecteur.
 *
 * Paramètres terminés par « _date » → date longue localisée ; par « _time » → heure.
 */
class InAppMessage
{
    /**
     * @param  array<string, scalar|null>  $params
     * @return array{key: string, params: array<string, scalar|null>, url: ?string, icon: string, tone: string}
     */
    public static function make(string $key, array $params = [], ?string $url = null, string $icon = 'bell', string $tone = 'navy'): array
    {
        return compact('key', 'params', 'url', 'icon', 'tone');
    }

    public static function text(DatabaseNotification $notification): string
    {
        $data = $notification->data;

        $params = collect($data['params'] ?? [])->map(function ($value, string $name) {
            return match (true) {
                $value === null => '',
                str_ends_with($name, '_date') => Format::dayDate($value),
                str_ends_with($name, '_time') => Format::time($value),
                default => $value,
            };
        })->all();

        return __($data['key'] ?? 'notifications.generic', $params);
    }
}
