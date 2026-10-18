<?php

namespace App\Models;

use App\Models\Concerns\FlushesCatalog;
use Database\Factories\RoomFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    /** @use HasFactory<RoomFactory> */
    use FlushesCatalog, HasFactory;

    protected $fillable = [
        'name',
        'capacity',
        'color',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }
}
