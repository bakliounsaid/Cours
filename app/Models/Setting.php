<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Réglage clé / valeur (jsonb). Lire et écrire via App\Settings\Settings.
 */
class Setting extends Model
{
    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'key',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'json',
        ];
    }
}
